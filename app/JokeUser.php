<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UserCard;

class JokeUser extends Model
{
    //
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'stripe_id' 
    ];

    public function cards(){
        return $this->hasMany(UserCard::class);
    }
}
