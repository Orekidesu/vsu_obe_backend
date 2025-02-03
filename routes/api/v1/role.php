<?php

use Illuminate\Support\Facades\Route;


// Admin Routes

Route::middleware('role:Admin')->group(function()
{
      // Admin Route
      Route::get('/admin/dashboard', function () {
          return response()->json(['message' => 'Welcome Admin']);
      });
});

// Dean Routes

Route::middleware('role:Dean')->group(function()
{
      // Admin Route
      Route::get('/dean/dashboard', function () {
          return response()->json(['message' => 'Welcome Dean']);
      });
});
