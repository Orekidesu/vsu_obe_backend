<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id'=> $request->role_id,
            'college_id'=>$request->college_id,
            'department_id'=>$request->department_id
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'message' => 'user registered successfully', 
            'credentials' =>[
            'full name' => $user->first_name . ' ' . $user->last_name,
            'username'=> $user->email,
            'role' => $user->role->name,
            ]
        ],201);
    }
}
