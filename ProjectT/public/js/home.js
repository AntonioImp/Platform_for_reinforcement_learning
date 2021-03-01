function startAI() {
    if(!start)
    {
        start = 1;
        drawChart([{episode: 1, value: 0}, {episode: 2, value: 0}], "#duration", "Duration");
        drawChart([{episode: 1, value: 0}, {episode: 2, value: 0}], "#reward", "Reward");
        document.querySelector(".diagrams").classList.remove("hidden");
        for(i = 1; i <= 20; i++)
        {
            document.querySelector("#video" + i).classList.add("hidden");
        }
        //console.log(select.value);
        request('/start', '', saveID);  //'game=' + select.value
    }
    else
    {
        document.querySelector(".error").textContent = "C'è già un allenamento attivo";
    }
}

function saveID(res) {
    ID = res;
    f_update = setInterval(update, '10000'); //5 minuti = 300000
}

function update() {
    request('/update', 'id=' + ID, showVid);
}

function showVid(res) {
    dataset = res;
    console.log(res);
    if(res != [])
    {
        document.querySelector("#duration").innerHTML = "";
        document.querySelector("#reward").innerHTML = "";
        let dataset_D = [];
        let dataset_R = [];
        let i = 0;
        for (let tmp of res)
        {
            dataset_D[i] = {
                episode: i + 1,
                value: parseInt(tmp.duration)
            }
            dataset_R[i] = {
                episode: ++i,
                value: parseInt(tmp.reward)
            }
        }
        drawChart(dataset_D, "#duration", "Duration");
        drawChart(dataset_R, "#reward", "Reward");

        pos = 0;
        if(res.length >= 20)
        {
            const range = (res.length - 1)/19;
            while(pos <= 19)
            {
                let video = document.querySelector("#video" + (pos + 1));
                video.src = "http://localhost/" + res[Math.round(pos * range)].image;
                video.dataset.position = Math.round(pos * range);
                video.classList.remove("hidden");
                pos++;
            }
        }
        else
        {
            while(pos <= res.length - 1)
            {
                let video = document.querySelector("#video" + (pos + 1));
                video.src = "http://localhost/" + res[pos].image;
                video.dataset.position = pos;
                video.classList.remove("hidden");
                pos++;
            }
        }
    }
}

function stopAI() {
    clearInterval(f_update);
    request('/stop', 'id=' + ID, '');
    start = 0;
    document.querySelector(".error").textContent = "";
}

function openModal(event) {
    document.body.classList.add("no-scroll");
    if(event.currentTarget.dataset.position != "")
    {
        let stream = document.querySelector(".video-js");
        stream.poster = "http://localhost/" + dataset[event.currentTarget.dataset.position].image;
        document.querySelector("source").src = "http://localhost/" + dataset[event.currentTarget.dataset.position].video;
        stream.load();
    }
    document.querySelector("#modal-view").classList.remove("hidden");
}

function removeModal(event) {
    document.body.classList.remove("no-scroll");
    event.currentTarget.classList.add("hidden");
}

function noClose(event) {
    event.stopPropagation();
}

function redirectArchive() {
    if(!start)
    {
        window.location.replace("/archive");
    }
    else
    {
        document.querySelector(".error").textContent = "Terminare l'allenamento prima di continuare";
    }
}

function viewElement(res) {
    console.log(res);
    if(res != -1)
    {
        ID = res;
        update();
        document.querySelector(".diagrams").classList.remove("hidden");
        document.querySelector(".video").classList.remove("hidden");
    }
}

document.querySelector("#start").addEventListener("click", startAI);
document.querySelector("#stop").addEventListener("click", stopAI);
document.querySelector("#modal-view").addEventListener("click", removeModal);

let start = 0;
let ID;
let f_update;
let dataset = [];
//let select = document.querySelector("select");

for(i = 1; i <= 20; i++)
{
    document.querySelector("#video" + i).addEventListener("click", openModal);
}

document.querySelector("video").addEventListener("click", noClose);
document.querySelector("#archive").addEventListener("click", redirectArchive);

//drawChart(dataset, tag sgv, asse y)
drawChart([], "#duration", "Duration");
drawChart([], "#reward", "Reward");
//drawChart([{episode: 1, value: 2},{episode: 2, value: 3},{episode: 3, value: 6},{episode: 4, value: 8},{episode: 5, value: 5},{episode: 6, value: 4},{episode: 7, value: 19},{episode: 8, value: 29}, {episode: 29, value: 31}], "#reward", "Reward");

let charge = document.querySelector("#charge");
if(charge.dataset.id != '')
{
    request("/idCheck", "id=" + charge.dataset.id, viewElement);
}
