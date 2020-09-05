<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //Table name
    protected $table='posts';
    //Primary key
    public $primaryKey ='id';
    //Timestamps
    public $timestamps = true;

    //model relationship , post belong to the user and has relationship with user
    public function user() {
        return $this->belongsTo('App\User');
    }
}
