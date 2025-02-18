<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
     return response([
      'data' => [
        'Username' => $request->user()->email,
        'Role' => $request->user()->role->name,  
      ]
    ]);
});

