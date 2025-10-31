<?php

namespace App\ApiPlatform\State;

use Illuminate\Support\MessageBag;
use Illuminate\Http\JsonResponse;

class ValidationErrorProvider
{

    public static function toJsonResponse(MessageBag $errors): JsonResponse
    {
        $violations = [];
        $detailMessages = [];

        foreach ($errors->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $violations[] = [
                    'propertyPath' => $field,
                    'message'      => $message,
                ];
                $detailMessages[] = "{$field}: {$message}";
            }
        }

        return new JsonResponse([
            'title'      => 'An error occurred',
            'detail'     => 'Validation errors: '.implode('; ', $detailMessages),
            'violations' => $violations,
            'status'     => 422,
        ], 422);
    }

}
