<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UserRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Admin,Department');
    }

    public function index()
    {
        //
        try {
            $currentUser = auth()->user();

            $users = User::query();
            if ($currentUser->role->name === 'Admin') {
                // Admin gets all users except admins

                $users->whereHas('role', function ($query) {
                    $query->where('roles.name', '!=', 'Admin');
                });
            } else {
                // as for the department:
                $users->whereHas('role', function ($query) {
                    $query->where('roles.name', '=', 'Faculty_Member');
                });
            }
            $result = $users->orderBy('first_name', 'asc')->get();
            return UserResource::collection($result)->additional([
                'message' => 'users retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {


        try {
            $validatedData = $request->validated();

            if (!isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make('password123');
            } else {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user = User::create($validatedData);

            return response()->json([
                'data' => new UserResource($user),
                'message' => 'user created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create a user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
            return response()->json([
                'data' => new UserResource($user),
                'message' => 'user retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve user',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        try {
            $validatedData = $request->validated();
            // $validatedData['password'] = Hash::make($validatedData['password']);

            $user->update($validatedData);

            return response()->json([
                'data' => new UserResource($user),
                'message' => 'user update successfully',

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to updated user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json([
                'message' => 'user deleted successfully',

            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(User $user)
    {
        try {
            $user->updateQuietly(['password' => Hash::make('passwordReset123')]);

            return response()->json([
                'message' => 'Password reset. Default password: passwordReset123',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}