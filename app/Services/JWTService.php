<?php
namespace App\Services;
//use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTService{
 
  public function generate($data) { 
    //$user = Auth::user();
    //$token = JWTAuth::fromUser($user); return $token; 
    $factory = JWTFactory::customClaims($data);
    $payload = $factory->make(); 
    $token = JWTAuth::encode($payload);
    return $token;
  } 

  public function verify() { 
    try { 
      $token=JWTAuth::getToken();
      $payload=JWTAuth::decode($token);
      return $payload;
    } catch(\Tymon\JWTAuth\Exceptions\JWTException $e) {
      return false; 
    } 
  } 
  public function refresh($token) {
    try { 
      $refreshedToken=JWTAuth::refresh($token); return $refreshedToken; 
    } catch(\Tymon\JWTAuth\Exceptions\JWTException $e) { 
      return false; 
    } 
  }
}