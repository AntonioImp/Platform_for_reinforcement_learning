<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 500);

use App\Models\History;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{
    public function startAI(Request $request)
    {
        $dir = dirname(dirname(getcwd()));
        chdir($dir);
        if ( file_exists("tmp") ) {
            echo('Directory tmp already exists!');
        } elseif(mkdir("tmp")){
            $new = new History;
            $new->PID = 0;
            $new->user_id = auth()->user()->id;
            $new->directory = $dir."\tmp";
            $new->active = true;
            $new->save();

            $id = History::select('id')->where('pid', 0)
                                       ->where('user_id', auth()->user()->id)
                                       ->where('active', true);
            $id =$id->get()->get(0)->id;
            rename($dir."\\tmp", $dir."\\".$id);

            //aggiorno il path della cartella
            $history = History::find($id);
            $history->directory = $dir."\\".$id;
            $history->save();

            // switch($request->game)
            // {
            //     case 1:
            //         $process = new Process(implode(" ", [
            //             'start "RL" /b C:\Users\enton\Anaconda3\python.exe',
            //             'C:\\xampp\\htdocs\\algoritmi_RL\\1.dqn.py',
            //             ''.$dir.'\\'.$id.''
            //         ]));
            //         break;
            //     case 2:
            //         $process = new Process(implode(" ", [
            //             'start "RL" /b C:\Users\enton\Anaconda3\python.exe',
            //             'C:\\xampp\\htdocs\\algoritmi_RL\\cart.dqn.py',
            //             ''.$dir.'\\'.$id.''
            //         ]));
            //         break;
            // }
            //['C:\\ProgramData\\Anaconda3\\python.exe', ''.$dir.'\\algoritmi_RL\\1.dqn.py', ''.$dir.'\\'.$id.'']
            
            // $command = implode(" ", [
            //     'start "RL" /b C:\ProgramData\Anaconda3\python.exe',
            //     ''.$dir.'\\algoritmi_RL\\1.dqn.py',
            //     ''.$dir.'\\'.$id.''
            // ]);

            // Log::info($command);

            $process = new Process([
                'C:\ProgramData\Anaconda3\python.exe',
                ''.$dir.'\\algoritmi_RL\\1.dqn.py',
                ''.$dir.'\\'.$id.''
            ]);
            $process->setOptions(['create_new_console' => true]);
            $process->start();

            $process->wait(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    Log::info('ERR > '.$buffer);
                } else {
                    Log::info('OUT > '.$buffer);
                }
            });

            echo $id;
        }
    }

    public function stopAI(Request $request)
    {
        $history = History::Find($request->id);
        if($history != null)
        {
            $history->active = false;
            $history->save();

            $pid = $history->PID;
            $process = Process::fromShellCommandline('taskkill /pid '.$pid.' /f');
            $process->run(function($type,$buffer){
                if (Process::ERR === $type) {
                    echo('ERR > '.$buffer);
                } else {
                    echo('OUT > '.$buffer);
                }
            });
        }
    }

    public function update(Request $request)
    {
        $dir = History::find($request->id);
        if($dir != null)
        {
            $dataset = [];
            $directories = glob($dir->directory.'\*', GLOB_ONLYDIR);
            sort($directories, SORT_NATURAL);

            foreach ($directories as $tmp)
            {
                if(file_exists($tmp.'\duration.txt'))
                {
                    if(file_exists($tmp.'\0.png'))
                    {
                        chdir($tmp);
                        $process = Process::fromShellCommandline('dir  /s /b *.png');
                        $process->run(function ($type, $buffer) use ($tmp){
                            if (Process::ERR === $type) {
                                dd('ERR > '.$buffer);
                            } else {
                                $buffer = explode("\r\n", $buffer);     //recupero i frame
                                unset($buffer[count($buffer) - 1]);
                                sort($buffer, SORT_NATURAL);            //riordino

                                //creo il file con i path dei frame
                                $concat = fopen($tmp.'\concat.txt', "w+");
                                foreach($buffer as $path)
                                {
                                    fwrite($concat, "file '".$path."'\n");
                                }
                                fclose($concat);

                                //creo il video dai frame
                                $process = Process::fromShellCommandline('ffmpeg -r 12 -f concat -safe 0 -i concat.txt game.mp4');
                                $process->run();
                            }
                        });

                        //elimino i frame già converiti tranne il primo
                        $process = Process::fromShellCommandline('rename 0.png index.jpeg');
                        $process->run();

                        $process = Process::fromShellCommandline('del /s *.png');
                        $process->run();

                        $process = Process::fromShellCommandline('del concat.txt');
                        $process->run();
                    }

                    //recupero i dati per i grafici
                    $duration = fopen($tmp.'\duration.txt', "r");
                    $reward = fopen($tmp.'\reward.txt', "r");
                    while(!feof($duration) && !feof($reward))
                    {
                        $dataset[] = [
                            "duration" => fgets($duration),
                            "reward" => fgets($reward),
                            "image" => substr($tmp, 16).'/index.jpeg',
                            "video" => substr($tmp, 16).'/game.mp4'
                        ];
                    }
                    fclose($duration);
                    fclose($reward);
                }
            }

            return $dataset;
        }
    }
}
