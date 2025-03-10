<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\JokeUser;

class UserCard extends Model
{
    //
    protected $fillable = [
        'stripe_id',
        'card_last_digits',
        'card_brand',
    ];
    
    public function jokeUser(){
        return $this->belongsTo(JokeUser::class);
    }
}
