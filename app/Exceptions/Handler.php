<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Authorizable;

class Handler extends ExceptionHandler
{

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */

     protected function unauthenticated($request, AuthenticationException $exception)
     {
        return response([
            'message'=> 'You are not authenticated. Please log in and try again.'
        ],401);
     }

      /**
     * Convert an authorization exception into an unauthorized response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\Access\AuthorizationException  $exception
     * @return \Illuminate\Http\Response
     */

     protected function unauthorized()
     {
        return response([
            'message'=>'You do not have the necessary permissions to access this resource.'
        ],403);
     }


    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
