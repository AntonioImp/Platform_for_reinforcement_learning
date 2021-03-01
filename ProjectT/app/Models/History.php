<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = "history";

    public function user() {
        return $this->belongsTo("App\Models\User");
    }
}
