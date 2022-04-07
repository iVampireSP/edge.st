<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (app()->environment() !== 'production') {
            $e = $exception;

            if ($e instanceof ModelNotFoundException) {
                $e = new NotFoundHttpException($e->getMessage(), $e);
            }

            // 全局错误处理
            if ($e instanceof ValidationException) {
                $response = [
                    "msg" => $e->getMessage(),
                    "code" => $e->status,
                    "data" => $e->errors(),
                    'status' => false

                ];
            } elseif ($e instanceof HttpException) {
                $response = [
                    "data" => Response::$statusTexts[$e->getStatusCode()] ?? null,
                    "code" => $e->getStatusCode(),
                    'status' => false

                ];
            } else {
                $response = [
                    'data' => $e->getMessage(),
                    'code' => 500,
                    'status' => false
                ];
            }

            if (env('APP_DEBUG', config('app.debug', false))) {
                $response['data'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->map(function ($trace) {
                        return Arr::except($trace, ['args']);
                    })->all(),
                ];
            }

            return response()->json($response, $response['code']);
        } else {
            return parent::render($request, $exception);
        }
        // 
        // return failed('Something wrong.', 0);

    }
}
