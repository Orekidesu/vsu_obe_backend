<?php

use App\Http\Controllers\Api\V1\Admin\DepartmentController;
use App\Http\Controllers\Api\V1\Admin\FacultyController;
use App\Http\Controllers\Api\V1\Admin\GraduateAttributeController;
use App\Http\Controllers\Api\V1\Admin\MissionController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\VisionController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Dean\ProposalReviewController;
use App\Http\Controllers\Api\V1\Department\CourseCategoryController;
use App\Http\Controllers\Api\V1\Department\CourseController;
use App\Http\Controllers\Api\V1\Department\CurriculumController;
use App\Http\Controllers\Api\V1\Department\CurriculumCourseController;
use App\Http\Controllers\Api\V1\Department\DepartmentRevisionController;
use App\Http\Controllers\Api\V1\Department\FetchDepartmentRevisionController;
use App\Http\Controllers\Api\V1\Department\GraduateAttributePeoController;
use App\Http\Controllers\Api\V1\Department\PeoMissionController;
use App\Http\Controllers\Api\V1\Department\ProgramController;
use App\Http\Controllers\Api\V1\Department\ProgramEducationalObjectiveController;
use App\Http\Controllers\Api\V1\Department\ProgramOutcomeController;
use App\Http\Controllers\Api\V1\Department\ProgramOutcomeGaController;
use App\Http\Controllers\Api\V1\Department\ProgramOutcomePeoController;
use App\Http\Controllers\Api\V1\Department\ProgramProposalController;
use App\Http\Controllers\Api\V1\Department\ProgramProposalWizardController;
use App\Http\Controllers\Api\V1\Department\SemesterController;
use App\Http\Controllers\Api\V1\Faculty\CommitteeRevisionController;
use App\Http\Controllers\Api\V1\Faculty\CourseDetailsWizardController;
use App\Http\Controllers\Api\V1\Faculty\FetchCommitteeRevisionController;
use App\Http\Controllers\Api\V1\Shared\CurriculumCoursePOController;
use App\Http\Controllers\Api\V1\Shared\FetchBothLevelRevisionController;
use App\Http\Controllers\Api\V1\Shared\ProgramProposalRevisionController;
use Illuminate\Support\Facades\Route;

// Admin Route List

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
  Route::get('/dashboard', function () {
    return response()->json(['message' => 'Welcome Dean']);
  });

  Route::apiResource('programs', ProgramController::class);
  Route::apiResource('program-proposals', ProgramProposalController::class);
  Route::apiResource('curriculum-courses', CurriculumCourseController::class);
  Route::post('/program-proposals/{programProposal}/review', [ProposalReviewController::class, 'review']);

  Route::get('/program-proposals/{program_proposal}/revisions', [FetchBothLevelRevisionController::class, 'fetchRevisions']);
});


// Department Route List
Route::middleware(['role:Department'])->prefix('department')->group(function () {
  Route::get('/department/dashboard', function () {
    return response()->json(['message' => 'Welcome Department']);
  });

  // Mission route:
  Route::apiResource('missions', MissionController::class);

  // GA Route
  Route::apiResource('graduate-attributes', GraduateAttributeController::class);


  // Program Routes
  Route::apiResource('programs', ProgramController::class);
  // PEO routes
  Route::apiResource('program-educational-objectives', ProgramEducationalObjectiveController::class);
  // PO routes
  Route::apiResource('program-outcomes', ProgramOutcomeController::class);

  // PEO-MISSION ROUTE
  Route::get('/peo-missions', [PeoMissionController::class, 'index']);
  Route::get('/peo-missions/{peo}', [PeoMissionController::class, 'show']);
  Route::post('/peo-missions/{peo}/attach', [PeoMissionController::class, 'attach']);
  Route::post('/peo-missions/{peo}/detach', [PeoMissionController::class, 'detach']);

  // GA-PEO ROUTE
  Route::get('/graduate-attribute-peos', [GraduateAttributePeoController::class, 'index']);
  Route::get('/graduate-attribute-peos/{graduate_attribute}', [GraduateAttributePeoController::class, 'show']);
  Route::post('/graduate-attribute-peos/{graduate_attribute}/attach', [GraduateAttributePeoController::class, 'attach']);
  Route::post('/graduate-attribute-peos/{graduate_attribute}/detach', [GraduateAttributePeoController::class, 'detach']);

  // PO-PEO
  Route::get('/program-outcome-peos', [ProgramOutcomePeoController::class, 'index']);
  Route::get('/program-outcome-peos/{program_outcome}', [ProgramOutcomePeoController::class, 'show']);
  Route::post('/program-outcome-peos/{program_outcome}/attach', [ProgramOutcomePeoController::class, 'attach']);
  Route::post('/program-outcome-peos/{program_outcome}/detach', [ProgramOutcomePeoController::class, 'detach']);

  // PO-GA
  Route::get('/program-outcome-gas', [ProgramOutcomeGaController::class, 'index']);
  Route::get('/program-outcome-gas/{program_outcome}', [ProgramOutcomeGaController::class, 'show']);
  Route::post('/program-outcome-gas/{program_outcome}/attach', [ProgramOutcomeGaController::class, 'attach']);
  Route::post('/program-outcome-gas/{program_outcome}/detach', [ProgramOutcomeGaController::class, 'detach']);

  // Curriculum
  Route::apiResource('curriculums', CurriculumController::class);

  // Course
  Route::apiResource('courses', CourseController::class);

  // Semester
  Route::apiResource('semesters', SemesterController::class);

  // Course Category
  Route::apiResource('course-categories', CourseCategoryController::class);

  // Curriculum Course
  Route::apiResource('curriculum-courses', CurriculumCourseController::class);

  // Users
  Route::apiResource('users', UserController::class);

  // Route to check if proposal is ready for review
  Route::patch(
    '/program-proposals/{programProposal}/check-ready-for-review',
    [ProgramProposalController::class, 'checkReadyForReview']
  );

  Route::patch('/program-proposals/{programProposal}/revise', [DepartmentRevisionController::class, 'handleDepartmentLevelRevision']);

  Route::get(
    '/program-proposals/{program_proposal}/revisions',
    [FetchDepartmentRevisionController::class, 'fetchRevisions']
  );
  // Proposal Revision Routes

  // Curriculum Course to PO
  // Route::apiResource('curriculum-course-po',CurriculumCourseP);
});


//=================== Department & Dean Route Program Proposal Controller =====================//

// Department Routes
Route::middleware(['role:Department'])->prefix('department')->group(function () {
  // Program Proposal Routes
  Route::get('/program-proposals', [ProgramProposalController::class, 'index']); // List all proposals
  Route::get('/program-proposals/{programProposal}', [ProgramProposalController::class, 'show']); // Show a specific proposal
  Route::post('/program-proposals', [ProgramProposalController::class, 'store']); // Create a new proposal
  // Program Proposal Wizard
  Route::post('/program-proposals/full-submit', [ProgramProposalWizardController::class, 'submit']);
});

// Dean Routes
Route::middleware(['role:Dean'])->prefix('dean')->group(function () {
  // Program Proposal Review Route
  // Route::patch('/program-proposals/{programProposal}/review', [ProgramProposalController::class, 'review']);
  // Review a proposal

});
//=================== Department & Dean Route Program Proposal Controller =====================//



//=================== Faculty Member Route List =====================//
Route::middleware(['role:Faculty_Member'])->prefix('faculty')->group(function () {

  // Curriculum Course Route
  Route::apiResource('curriculum-courses', CurriculumCourseController::class);


  Route::get('/curriculum-courses/{curriculum_course}/program-outcomes', [CurriculumCoursePOController::class, 'getProgramOutcomes']);

  Route::patch('/curriculum-courses/{curriculum_course}/revise', [CommitteeRevisionController::class, 'handleCommitteeLevelRevision']);

  Route::post('/curriculum-courses/submit', [CourseDetailsWizardController::class, 'submit']);

  // Committee Revision Fetch Route
  Route::get(
    'curriculum-courses/{curriculum_course}/revisions',
    [FetchCommitteeRevisionController::class, 'fetchRevisions']
  );
});