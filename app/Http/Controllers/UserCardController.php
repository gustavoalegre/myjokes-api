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
            try{
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
            } catch (\Illuminate\Validation\ValidationException $th) {
                return response()->json(['status' => 400, 'error' => $th->validator->errors()], 400);
            }

            $input = $request->all();

            // Envía los datos a Stripe
            $stripe = new \Stripe\StripeClient(config('stripe.sk'));
            // Payment Method
            $pm = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                  /*'number' => $input['number'],
                  'exp_month' => $input['exp_month'],
                  'exp_year' => $input['exp_year'],
                  'cvc' => $input['cvc']*/
                  'token' => 'tok_visa'
                ]
            ]);
            // Asociar payment method a customer
            $cus_pm = $stripe->paymentMethods->attach(
                $pm->id,
                ['customer' => $joke_user->stripe_id]
            );

            // Guarda los datos de la tarjeta en la bd
            $user_card = new UserCard;
            $user_card->stripe_id = $cus_pm->id;
            $user_card->card_last_digits = substr($input['number'], -4);
            $user_card->card_brand = $input['brand'];
            $user_card->joke_user_id = $joke_user->id;
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

    /**
     * Procesar un cargo a la tarjeta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function charge(Request $request){
        // Obtener usuario
        $tokenData = app(JWTService::class)->verify();
        $joke_user = JokeUser::where('email', $tokenData['sub'])->first();
        if ($joke_user){
            // Validar ingreso de datos
            try{
                $request->validate([
                    'card_id' => 'required',
                    'amount' => 'required'
                ], [
                    'card_id.required' => 'Se requiere el id de Stripe de la tarjeta',
                    'amount.required' => 'Se requiere monto a cargar',
                ]);
            } catch (\Illuminate\Validation\ValidationException $th) {
                return response()->json(['status' => 400, 'error' => $th->validator->errors()], 400);
            }

            $input = $request->all();

            // Obtener stripe_id del customer asociado a la tarjeta
            $customer_id = UserCard::where('stripe_id', $input['card_id'])->first()->jokeUser->stripe_id;
            // Valida que la tarjeta pertenezca al usuario logueado
            if ($customer_id == $joke_user->stripe_id){
                // Envía los datos a Stripe
                $stripe = new \Stripe\StripeClient(config('stripe.sk'));
                // Charge (deprecated)
                /*$charge = $stripe->charges->create([
                    'amount' => $input['amount'],
                    'currency' => 'usd',
                    //'source' => $input['card_id'],
                    'customer' => $customer_id,
                    'description' => 'Charge for ' . $joke_user->email
                ]);*/
                // Payment Intent
                $paymentIntent = $stripe->paymentIntents->create([
                    'amount' => $input['amount'],
                    'currency' => 'usd',
                    'customer' => $customer_id,
                    'payment_method' => $input['card_id'],
                    'description' => 'Charge for ' . $joke_user->email
                ]);

                return response()->json($paymentIntent, 201);
            } else {
                //Tarjeta no pertenece al usuario
                return response()->json(['status' => 403, 'message' => "La tarjeta no pertenece al usuario"], 403);
            }
        } else {
            //Usuario inválido
            return response()->json(['status' => 400, 'message' => "Usuario inválido"], 400);
        }
    }
}
