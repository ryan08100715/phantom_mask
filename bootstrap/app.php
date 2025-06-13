<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions
            ->render(function (NotFoundHttpException $e, Request $request) {
                if ($request->is('api/*')) {
                    // 處理模型查無資料
                    if ($e->getPrevious() instanceof ModelNotFoundException) {
                        /** @var ModelNotFoundException $modelNotFound */
                        $modelNotFound = $e->getPrevious();

                        return response()->json([
                            'message' => $modelNotFound->getMessage(),
                            'errors' => [
                                [
                                    'code' => 'resource_not_found',
                                    'detail' => $modelNotFound->getMessage(),
                                ],
                            ],
                        ], 404);
                    }
                }
            })
            ->render(function (ValidationException $e, Request $request) {
                if ($request->is('api/*')) {
                    // 處理請求參數驗證錯誤
                    $errors = [];
                    foreach ($e->errors() as $error) {
                        foreach ($error as $errorMessage) {
                            $errors[] = [
                                'code' => 'invalid_format',
                                'detail' => $errorMessage,
                            ];
                        }
                    }

                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $errors,
                    ], 422);
                }
            })
            ->render(function (Exception $e, Request $request) {
                if ($request->is('api/*')) {
                    // 處理剩下的錯誤
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => [
                            [
                                'code' => 'server_error',
                                'detail' => $e->getMessage(),
                            ],
                        ],
                    ], 500);
                }
            });
    })->create();
