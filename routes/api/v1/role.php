<?php

use App\Http\Controllers\Api\V1\Admin\MissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\VisionController;


// Admin Routes

Route::middleware(['role:Admin'])->prefix('admin')->group(function()
{
    //   sample route
      Route::get('/admin/dashboard', function () {
          return response()->json(['message' => 'Welcome Admin']);
      });
      
    //   Vision Route
      Route::apiResource('visions',VisionController::class);
    //   Mission Route
      Route::apiResource('missions',MissionController::class);


});

// Dean Routes

Route::middleware(['role:Dean'])->prefix('dean')->group(function()
{
      Route::get('/dean/dashboard', function () {
          return response()->json(['message' => 'Welcome Dean']);
      });
});


// Department Routes
Route::middleware(['role:Department'])->prefix('department')->group(function()
{
      Route::get('/department/dashboard', function () {
          return response()->json(['message' => 'Welcome Dean']);
      });
});
