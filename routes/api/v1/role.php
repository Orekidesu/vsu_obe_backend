<?php

use Illuminate\Support\Facades\Route;


Route::middleware('role:Admin')->group(function()
{
      // Admin Route
      Route::get('/admin/dashboard', function () {
          return response()->json(['message' => 'Welcome Admin']);
      });
});
