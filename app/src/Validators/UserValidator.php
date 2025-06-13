<?php
// valide les données des requêtes
namespace App\Validators;

use App\Utils\HttpException;
use App\Utils\JWT;

class UserValidator {
   
    public static function validateId(mixed $id): int {
        $id = intval($id);
        if ($id <= 0) {
            throw new HttpException("Invalid user ID.", 400);
        }
        return $id;
    }

    public static function validateUpdateFields(array $data, array $allowedFields): array {
        if (empty($data)) {
            throw new HttpException("No data provided for the update.", 400);
        }

        $filtered = array_filter(
            $data,
            fn($key) => in_array($key, $allowedFields),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($filtered)) {
            throw new HttpException("No valid fields provided for the update.", 400);
        }

        return $filtered;
    }

    public static function requireFields(array $data, array $requiredFields): void {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new HttpException("Missing field: {$field}", 400);
            }
        }
    }
}
