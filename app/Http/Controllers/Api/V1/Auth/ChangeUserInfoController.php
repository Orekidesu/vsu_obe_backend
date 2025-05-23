<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangeUserInfoRequest;
use Exception;
use Illuminate\Http\Request;

class ChangeUserInfoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware("role:Admin,Department,Dean,Faculty_Member");
    }

    public function store(ChangeUserInfoRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = auth()->user();

            if (isset($validated['first_name'])) {
                $user->first_name = $validated['first_name'];
            }
            if (isset($validated['last_name'])) {
                $user->last_name = $validated['last_name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            $user->save();

            return response()->json([
                'message' => 'user info changed successfully',

            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to change user info',
                'errr' => $e->getMessage(),
            ], 500);
        }
    }
}