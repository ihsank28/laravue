<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['title', 'description', 'path', 'encoded'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
