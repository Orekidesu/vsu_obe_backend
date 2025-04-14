<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramProposalWizardRequest;
use Illuminate\Http\Request;

class ProgramProposalWizardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }


    public function submit(ProgramProposalWizardRequest $request) {}
    //
}