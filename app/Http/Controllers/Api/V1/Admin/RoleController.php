<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Exception;

class RoleController extends Controller
{
    //

    public function __construct()
    {

        $this->middleware('auth:sanctum');
        $this->middleware('role:Admin');
    }

    public function index()
    {
        try {
            $roles = Role::all();
            return response()->json([
                'data' => $roles,
                'message' => 'roles retrieved successfuully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve roles',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
