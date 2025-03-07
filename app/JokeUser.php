<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JokeUser extends Model
{
    //
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number'    
    ];

    public function cards(){
        return $this->hasMany('UserCard');
    }
}
