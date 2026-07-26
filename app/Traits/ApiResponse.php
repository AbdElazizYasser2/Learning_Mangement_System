<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

trait ApiResponse
{
    protected function success(string $message = 'Success', mixed $data = null, int $code = 200): JsonResponse
    {
        $response = ['message' => $message];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function error(string $message = 'Something went wrong', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = ['message' => $message];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function created(string $message = 'Created successfully', mixed $data = null): JsonResponse
    {
        return $this->success($message, $data, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function paginated(AnonymousResourceCollection $collection, string $message = 'Data retrieved successfully', int $statusCode = 200): JsonResponse
    {
        $paginatedData = $collection->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginatedData['data'] ?? [],
            'links'   => $paginatedData['links'] ?? null,
            'meta'    => $paginatedData['meta'] ?? null,
        ], $statusCode);
    }
}