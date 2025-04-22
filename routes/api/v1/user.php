<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
  return response([
    'data' => [
      'Email' => $request->user()->email, //Username Before
      'First_Name' => $request->user()->first_name,
      'Last_Name' => $request->user()->last_name,
      'Role' => $request->user()->role->name,
      'Department' => $request->user()->department ? $request->user()->department : null,
      'Faculty' => !($request->user()->department) ? $request->user()->faculty : null,
    ]
  ]);
});