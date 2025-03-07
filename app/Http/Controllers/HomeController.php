<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Services\JWTService;
use App\JokeUser;

class HomeController extends Controller
{
    /**
     * Inicia sesión.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Consultar por existencia del usuario
        $joke_user = JokeUser::where('email', $request->input('email'))->where('password', md5($request->input('password')))->first();
        if ($joke_user){
            $userData =[
                'sub' => $joke_user->email,
                'first_name' => $joke_user->first_name,
                'last_name' => $joke_user->last_name
            ];
            $token = app(JWTService::class)->generate($userData);

            return response()->json(['status' => 200, 'token' => "{$token}"], 200);
        } else {
            return response()->json(['status' => 401, 'message' => 'BAD CREDENTIALS'], 401);
        }
        
    }

    /**
     * Home.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function home(Request $request){
        // Obtener usuario
        $tokenData = app(JWTService::class)->verify();
        $joke_user = JokeUser::where('email', $tokenData['sub'])->first();
        if (count($joke_user->cards) > 0){
            // Cargar broma
            $url = "https://sv443.net/jokeapi/v2/joke/Any";
        
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Accept' => 'application/json',
            ])->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                // Process the data
                return response($response)->withHeaders([
                    'Content-Type' => 'application/json'
                ]);
            } else {
                // Handle the error
                return response()->json(['status' => 200, 'message' => ":)"], 200);
            }
        } else {
            return response()->json(['status' => 200, 'message' => "PENDING_CARD"], 200);
        }
    }
}
