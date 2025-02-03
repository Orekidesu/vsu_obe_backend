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
        $token =$request->user()->createToken('api-token')->plainTextToken;


        return response(
            [
                'user' => $request->user(),
                'token' => $token,
            ]
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        
        // Auth::guard('web')->logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();

        // return response()->noContent();
        $request->user()->tokens()->delete();

        return response([
            'message' => 'logout successfully'
        ]);
    }
}
