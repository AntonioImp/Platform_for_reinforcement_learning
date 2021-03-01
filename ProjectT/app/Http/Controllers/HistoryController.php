<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HistoryController extends Controller
{
    public function load()
    {
        $training = History::where('user_id', auth()->user()->id);
        $i = 0;
        $data = [];
        foreach($training->get() as $value)
        {
            $data[$i] = [
                "id" => $value->id,
                "date" => date_format($training->get()->get($i)->created_at, 'Y/m/d H:i:s')
            ];
            $i++;
        }
        if($data == [])
            return 0;
        else
            return $data;
    }

    public function delete(Request $request)
    {
        $row = History::find($request->id);
        $process = Process::fromShellCommandline('rd /s /q '.$row->directory);
        $process->run();
        $row->delete();

        return 1;
    }

    public function idCheck(Request $request)
    {
        $row = History::find($request->id);
        if($row != null && $row->user_id == auth()->user()->id)
            return $request->id;
        else
            return -1;
    }
}
