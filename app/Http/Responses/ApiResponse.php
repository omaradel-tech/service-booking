<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    public static function success($data = null, $meta = null, int $status = 200): JsonResponse
    {
        $response = ['data' => $data];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    public static function error(string $code, string $message, $details = null, int $status = 400): JsonResponse
    {
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $status);
    }

    public static function paginated(JsonResource $resource): JsonResponse
    {
        return response()->json([
            'data' => $resource->collection,
            'meta' => [
                'pagination' => [
                    'current_page' => $resource->resource->currentPage(),
                    'last_page' => $resource->resource->lastPage(),
                    'per_page' => $resource->resource->perPage(),
                    'total' => $resource->resource->total(),
                    'from' => $resource->resource->firstItem(),
                    'to' => $resource->resource->lastItem(),
                    'has_more' => $resource->resource->hasMorePages(),
                ],
            ],
        ]);
    }
}
