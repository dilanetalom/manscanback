<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'prenom'=> 'required|string|max:255',
            'telephone'=> 'required|string|max:255',
            'statut'=> 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response([$response, ], 200);
    }


    public function getUser()
    {
        $user = User::all();
        return response()->json([
            "data" => $user,
            "message" => "get user success"
        ]);
    }


    public function getbyiduser($id)
    { 
        $user = User::find($id);
        return response()->json([
            "data" => $user,
            "message" => "get user success"
        ]);
    }


    // public function update( Request $request, $id)
    // { 
    //     $user = User::find($id);
    //     $user = User::update([
    //         "name" => $request->name,
    //         "email" => $request->email,
    //         "password" => $request->password,
    //         "prenom" => $request->prenom,
    //         "telephone" => $request->telephone,
    //         "statut" => $request->statut,
    //     ]);

    //     return response($user, 200);
    // }

    public function update(Request $request, $id)
{
    $user = User::find($id);
    
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->prenom = $request->prenom;
    $user->telephone = $request->telephone;
    $user->statut = $request->statut;
    
    $user->save();
    
    return response($user, 200);
}



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
           
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response()->json([
                    "token" => $response,
                    "statut" => "200",
                    "user" => $user,
                ]);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 422);
        }
    }


    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response()->json([
            'statut' => "200",
            'message'=>$response
        ]);
    }


   

    public function user()
    {
        $user = Auth::user();
        $presence = Presence::where('user_id', $user->id)->orderByDesc('created_at')->get();
        return response()->json([
            'presence' => $presence
        ]);
    }
}
