<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        // $request->session()->regenerate();

        if(!Auth::check())
        {
            return response()->json(
                [
                    'message' => 'Authentication failed'
                ],
                401
            );
        }
        $token =$request->user()->createToken('api-token')->plainTextToken;


        return response(
            [
                'user' => $request->user(),
                'token' => $token,
                'message' => "login in successfully",
            ],200
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        
        if(Auth::check())
        {
            return response()->json(
                [
                    'message' => 'User is not logged in'   
                ],401
                
            );
        }

        $request->user()->tokens()->delete();

        return response([
            'message' => 'logout successfully'
        ]);
    }
}
