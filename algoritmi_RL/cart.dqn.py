#!/usr/bin/env python
# coding: utf-8

# In[1]:


import math, random

import gym
import numpy as np
import cv2
import torch
import torch.nn as nn
import torch.optim as optim
import torch.autograd as autograd 
import torch.nn.functional as F
import sys
import os
from os.path import join


# Collegamento database
import mysql.connector

mydb = mysql.connector.connect(
	host="localhost",
	user="root",
	passwd="",
	database="project_t"
)

mycursor = mydb.cursor()
sql = "UPDATE history SET PID = %s WHERE directory = %s"
val = (str(os.getpid()), sys.argv[1])
mycursor.execute(sql, val)
mydb.commit()


# In[2]:


from IPython.display import clear_output
import matplotlib.pyplot as plt


# <h3>Use Cuda</h3>

# In[3]:


USE_CUDA = torch.cuda.is_available()
Variable = lambda *args, **kwargs: autograd.Variable(*args, **kwargs).cuda() if USE_CUDA else autograd.Variable(*args, **kwargs)


# <h2>Replay Buffer</h2>

# In[5]:


from collections import deque

class ReplayBuffer(object):
    def __init__(self, capacity):
        self.buffer = deque(maxlen=capacity)
    
    def push(self, state, action, reward, next_state, done):
        state      = np.expand_dims(state, 0)
        next_state = np.expand_dims(next_state, 0)
            
        self.buffer.append((state, action, reward, next_state, done))
    
    def sample(self, batch_size):
        state, action, reward, next_state, done = zip(*random.sample(self.buffer, batch_size))
        return np.concatenate(state), action, reward, np.concatenate(next_state), done
    
    def __len__(self):
        return len(self.buffer)


# <h2>Cart Pole Environment</h2>

# In[6]:


env_id = "CartPole-v0"
env = gym.make(env_id)


# <h2>Epsilon greedy exploration</h2>

# In[7]:


epsilon_start = 1.0
epsilon_final = 0.01
epsilon_decay = 500

epsilon_by_frame = lambda frame_idx: epsilon_final + (epsilon_start - epsilon_final) * math.exp(-1. * frame_idx / epsilon_decay)


# <h2>Deep Q Network</h2>

# In[9]:


class DQN(nn.Module):
    def __init__(self, num_inputs, num_actions):
        super(DQN, self).__init__()
        
        self.layers = nn.Sequential(
            nn.Linear(env.observation_space.shape[0], 128),
            nn.ReLU(),
            nn.Linear(128, 128),
            nn.ReLU(),
            nn.Linear(128, env.action_space.n)
        )
        
    def forward(self, x):
        return self.layers(x)
    
    def act(self, state, epsilon):
        if random.random() > epsilon:
            state   = Variable(torch.FloatTensor(state).unsqueeze(0), volatile=True)
            q_value = self.forward(state)
            action  = q_value.max(1)[1].item()
        else:
            action = random.randrange(env.action_space.n)
        return action


# In[10]:


torch.cuda.current_device()
model = DQN(env.observation_space.shape[0], env.action_space.n)

if USE_CUDA:
    model = model.cuda()
    
optimizer = optim.Adam(model.parameters())

replay_buffer = ReplayBuffer(1000)


# <h2>Computing Temporal Difference Loss</h2>

# In[11]:


def compute_td_loss(batch_size):
    state, action, reward, next_state, done = replay_buffer.sample(batch_size)

    state      = Variable(torch.FloatTensor(np.float32(state)))
    next_state = Variable(torch.FloatTensor(np.float32(next_state)), volatile=True)
    action     = Variable(torch.LongTensor(action))
    reward     = Variable(torch.FloatTensor(reward))
    done       = Variable(torch.FloatTensor(done))

    q_values      = model(state)
    next_q_values = model(next_state)

    q_value          = q_values.gather(1, action.unsqueeze(1)).squeeze(1)
    next_q_value     = next_q_values.max(1)[0]
    expected_q_value = reward + gamma * next_q_value * (1 - done)
    
    loss = (q_value - Variable(expected_q_value.data)).pow(2).mean()
        
    optimizer.zero_grad()
    loss.backward()
    optimizer.step()
    
    return loss


# <h2>Training</h2>

# In[13]:


num_frames = 1000
batch_size = 32
gamma      = 0.99

losses = []
all_rewards = []
episode_reward = 0

episode_num = 1
episode_dur = 0

state = env.reset()

train = sys.argv[1] + '\\'
os.makedirs(train + str(episode_num))  #exist_ok=True   evita il blocco errore se la cartella esiste

for frame_idx in range(1, num_frames + 1):
    epsilon = epsilon_by_frame(frame_idx)
    action = model.act(state, epsilon)
    
    next_state, reward, done, _ = env.step(action)
    replay_buffer.push(state, action, reward, next_state, done)
	
    state = (torch.FloatTensor(state)).squeeze(0)
    
    img = state.numpy().astype(np.uint8)
    #img[img < 128] = 0
    #img[img > 0] = 255
    img = cv2.rotate(img, 0)
    img = cv2.flip(img, 1)
    img = cv2.resize(img, (120, 120))
    path = train + str(episode_num) + "\\" + str(episode_dur) + '.png'
    cv2.imwrite(path, img)
    
    episode_dur += 1
    
    state = next_state
    episode_reward += reward
    
    if done:
        f = open(train + str(episode_num) + "\\reward.txt", "w+")
        f.write(str(episode_reward))
        f.close()
        f = open(train + str(episode_num) + "\\duration.txt", "w+")
        f.write(str(episode_dur))
        f.close()
		
        episode_dur = 0
        episode_num += 1
        os.mkdir(train + str(episode_num))
		
        state = env.reset()
        all_rewards.append(episode_reward)
        episode_reward = 0
        
    if len(replay_buffer) > batch_size:
        loss = compute_td_loss(batch_size)
        losses.append(loss.item())
