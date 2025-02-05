<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
     return response([
      'data' => [
        'Username' => $request->user()->email,
        'Password' => $request->user()->password,
        'Role' => $request->user()->role->name,
         
      ]
    ]);
});

