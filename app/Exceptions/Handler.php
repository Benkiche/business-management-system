<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        BusinessException::class,
        ValidationException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        $this->renderable(function (BusinessException $e, $request) {
            if ($request->expectsJson()) {
                return $e->render();
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return $e->render();
            }

            return back()
                ->withInput()
                ->withErrors($e->getErrors());
        });
    }
}