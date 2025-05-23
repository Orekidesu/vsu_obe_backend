<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangeUserPasswordRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangeUserPasswordController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware("role:Admin,Department,Dean,Faculty_Member");
    }

    public function store(ChangeUserPasswordRequest $request)
    {

        try {
            $validated = $request->validated();

            $user = auth()->user();

            $user->password = Hash::make($validated['password']);

            $user->save();

            return response()->json([
                'message' => 'Password Changed Successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to change password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}