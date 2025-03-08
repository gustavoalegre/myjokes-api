<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\JokeUser;

class JokeUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Lista los usuarios
        $joke_users = JokeUser::all();
        return response()->json($joke_users);;
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
        // Validar ingreso
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5',
            'phone_number' => 'required'
        ], [
            'first_name.required' => 'Se requiere el nombre',
            'last_name.required' => 'Se requiere el apellido',
            'email.required' => 'Se requiere el e-mail',
            'password.required' => 'Se requiere la contraseña',
            'phone_number.required' => 'Se requiere el número de teléfono',
        ]);
        $input = $request->all();

        // Envía los datos a Stripe para crear el customer
        $stripe = new \Stripe\StripeClient(config('stripe.sk'));
        $customer = $stripe->customers->create([
            'name' => $input['first_name'] . ' ' . $input['last_name'],
            'email' => $input['email'],
        ]);

        // Crea un nuevo usuario
        $joke_user = new JokeUser;
        $joke_user->first_name = $input['first_name'];
        $joke_user->last_name = $input['last_name'];
        $joke_user->email = $input['email'];
        $joke_user->password = md5($input['password']);
        $joke_user->phone_number = $input['phone_number'];
        $joke_user->stripe_id = $customer->id;
        $joke_user->save();
        return response()->json(['status' => 201, 'message' => 'created'], 201);
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
