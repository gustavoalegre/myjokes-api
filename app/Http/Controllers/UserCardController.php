<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JWTService;
use App\JokeUser;
use App\UserCard;

class UserCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtener usuario
        $tokenData = app(JWTService::class)->verify();
        if ($tokenData){
            $joke_user = JokeUser::where('email', $tokenData['sub'])->first();
            return response()->json(['status' => 200, 'data' => $joke_user->cards], 200);
        } else {
            //Usuario inválido
            return response()->json(['status' => 400, 'message' => "Usuario inválido"], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Obtener usuario
        $tokenData = app(JWTService::class)->verify();
        $joke_user = JokeUser::where('email', $tokenData['sub'])->first();
        if ($joke_user){
            // Validar ingreso
            $request->validate([
                'brand' => 'required',
                'number' => 'required',
                'exp_month' => 'required',
                'exp_year' => 'required',
                'cvc' => 'required'
            ], [
                'brand.required' => 'Se requiere la marca de la tarjeta',
                'number.required' => 'Se requiere el número de la tarjeta',
                'exp_month.required' => 'Se requiere el mes de vencimiento de la tarjeta',
                'exp_year.required' => 'Se requiere el año de vencimiento de la tarjeta',
                'cvc.required' => 'Se requiere el CVC de la tarjeta',
            ]);
            $input = $request->all();

            // Envía los datos a Stripe
            $stripe = new \Stripe\StripeClient(config('stripe.sk'));
            // Customer
            $customer = $stripe->customers->create([
                'name' => $joke_user->first_name . ' ' . $joke_user->last_name,
                'email' => $joke_user->email,
            ]);
            // Payment Method
            $pm = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                  'number' => $input['number'],
                  'exp_month' => $input['exp_month'],
                  'exp_year' => $input['exp_year'],
                  'cvc' => $input['cvc']
                ]
            ]);
            // Asociar payment method a customer
            $cus_pm = $stripe->paymentMethods->attach(
                $pm->id,
                ['customer' => $customer->id]
            );

            // Guarda los datos de la tarjeta en la bd
            $user_card = new UserCard;
            $user_card->stripe_id = $cus_pm->id;
            $user_card->card_last_digits = substr($input['number'], -4);
            $user_card->card_brand = $input['brand'];
            $user_card->save();

            return response()->json($cus_pm, 201);
        } else {
            //Usuario inválido
            return response()->json(['status' => 400, 'message' => "Usuario inválido"], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
