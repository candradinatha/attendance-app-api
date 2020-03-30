<?php 

namespace App\Traits;

use Exception;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

trait ApiHandlerTrait
{
    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
                    ? response()->json([
                        'error'  => [
                            'code' => 401,
                            'title' => $exception->getMessage(),
                            'errors' => [
                                [
                                    'title' => 'auth',
                                    'message' => $exception->getMessage()
                                ]
                            ]
                        ]
                    ], 401)
                    // : redirect()->guest(route('login'));
                    : redirect()->guest(
                        $exception->redirectTo() ?? 
                            route(array_get($exception->guards(), 0) . '.login')
                        );
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $errors = [];

        foreach ($exception->errors() as $key => $message) {
            $errors[] = [
                'title' => $key,
                'message' => $message[0]
            ];
        }

        return response()->json([
            'error' => [
                'code'   => $exception->status,
                'title'  => $exception->getMessage(),
                'errors' => $errors,
            ]
        ], $exception->status);
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];
        
        $errorsArray = [
            'error' => [
                'code'  => $status,
                'title' => class_basename(get_class($e)),
                // 'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
                'errors' => [
                    [
                        'title' => class_basename(get_class($e)),
                        'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
                    ]
                ]
            ]
        ];

        if (config('app.debug')) {
            $errorsArray = array_merge($errorsArray, $this->convertExceptionToArray($e));
        }

        return new JsonResponse(
            $errorsArray, 
            $status, $headers,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}