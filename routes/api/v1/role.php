<?php

use App\Http\Controllers\Api\V1\Admin\DepartmentController;
use App\Http\Controllers\Api\V1\Admin\FacultyController;
use App\Http\Controllers\Api\V1\Admin\GraduateAttributeController;
use App\Http\Controllers\Api\V1\Admin\MissionController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\VisionController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Department\GraduateAttributePeoController;
use App\Http\Controllers\Api\V1\Department\PeoMissionController;
use App\Http\Controllers\Api\V1\Department\ProgramController;
use App\Http\Controllers\Api\V1\Department\ProgramEducationalObjectiveController;
use App\Http\Controllers\Api\V1\Department\ProgramOutcomeController;
use App\Http\Requests\Api\V1\Department\PeoMissionRequest;
use Illuminate\Support\Facades\Route;

// Admin Routes

Route::middleware(['role:Admin'])->prefix('admin')->group(function () {
  //   sample route
  Route::get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome Admin']);
  });

  // Role Route
  Route::get('roles', [RoleController::class, 'index']);

  //   Vision Route
  Route::apiResource('visions', VisionController::class);
  //   Mission Route
  Route::apiResource('missions', MissionController::class);
  //   Graduate Attribute Route
  Route::apiResource('graduate-attributes', GraduateAttributeController::class);
  //  Faculty Route
  Route::apiResource('faculties', FacultyController::class);
  //  Department Route
  Route::apiResource('departments', DepartmentController::class);

  // User Management Route
  Route::apiResource('users', UserController::class);
  Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
});

// Dean Routes

Route::middleware(['role:Dean'])->prefix('dean')->group(function () {
  Route::get('/dean/dashboard', function () {
    return response()->json(['message' => 'Welcome Dean']);
  });
});


// Department Routes
Route::middleware(['role:Department'])->prefix('department')->group(function () {
  Route::get('/department/dashboard', function () {
    return response()->json(['message' => 'Welcome Department']);
  });

  // Program Routes
  Route::apiResource('programs', ProgramController::class);
  // PEO routes
  Route::apiResource('program-educational-objectives', ProgramEducationalObjectiveController::class);
  // PO routes
  Route::apiResource('program-outcomes', ProgramOutcomeController::class);

  // PEOMISSION ROUTE
  Route::get('/peo-missions', [PeoMissionController::class, 'index']);
  Route::get('/peo-missions/{peo}', [PeoMissionController::class, 'show']);
  Route::post('/peo-missions/{peo}/attach', [PeoMissionController::class, 'attach']);
  Route::post('/peo-missions/{peo}/detach', [PeoMissionController::class, 'detach']);

  // GAPEO ROUTE
  Route::get('/graduate-attribute-peos', [GraduateAttributePeoController::class, 'index']);
  Route::get('/graduate-attribute-peos/{graduate_attribute}', [GraduateAttributePeoController::class, 'show']);
  Route::post('/graduate-attribute-peos/{graduate_attribute}/attach', [GraduateAttributePeoController::class, 'attach']);
  Route::post('/graduate-attribute-peos/{graduate_attribute}/detach', [GraduateAttributePeoController::class, 'detach']);
});
