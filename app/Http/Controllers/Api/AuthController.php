<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email'=> 'required|email|unique:users,email',
                'password'=>'required|string|min:6',
                'phone'=>'nullable|string|max:20',
            ]);    
        } catch (ValidationException $e){
            return response()->json([
                'message'=>'Failed to register user',
                'errors'=>$e->errors()
            ], 422);
        }

        $validated['password']=Hash::make($validated['password']);
        $validated['remember_token']=Str::random(10);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user'=> [
                'id' => $user->id,
                'name'=> $user->name,
                'email'=> $user->email,
                'phone'=>$user->phone,

            ]
        ], 201);

    }
}
