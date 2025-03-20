<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
  return response()->json(['status' => 'Super Ultra Mega Healthy']);
});

// Load API versioned routes dynamically
require __DIR__ . '/api/v1.php';
