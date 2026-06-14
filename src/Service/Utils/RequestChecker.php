<?php

declare(strict_types=1);

namespace App\Service\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

class RequestChecker
{
    public function checkEmail(string $email): ?JsonResponse
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Invalid email format.', 'status' => 400], 400);
        }

        return null;
    }

    public function checkRequiredFields(array $data, array $fields): ?JsonResponse
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse(['message' => "Missing required field: $field", 'status' => 400], 400);
            }
        }

        return null;
    }
}
