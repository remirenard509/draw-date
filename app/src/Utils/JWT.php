<?php

namespace App\Utils;

class JWT {
    private static $secret;

    private static function getSecret() {
        if (!self::$secret) {
            self::$secret = getenv('JWT_SECRET');
        }
        return self::$secret;
    }

    public static function generate($payload) {
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64UrlEncode(json_encode($payload));

        $signatureInput = "$header.$payload";
        $signature = hash_hmac("sha256", $signatureInput, self::getSecret(), true);
        $signature = self::base64UrlEncode($signature);

        return "$header.$payload.$signature";
    }

    public static function verify($jwt) {
        $segments = explode('.', $jwt);
        if (count($segments) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $segments;
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", self::getSecret(), true)
        );

        return hash_equals($expectedSignature, $signature);
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function getPayLoad($jwt) {
        $segments = explode('.', $jwt);
        if (count($segments) !== 3) {
            return null;
        }

        list(, $payload) = $segments;
        return json_decode(self::base64UrlDecode($payload), true);
    }

    public static function isExpired($jwt) {
        $payload = self::getPayLoad($jwt);
        return $payload && isset($payload['exp']) ? $payload['exp'] < time() : true;
    }

    public static function validateWithIdAndExpiry($jwt, $expectedId) {
        if (!self::verify($jwt)) {
            return false;
        }

        if (self::isExpired($jwt)) {
            return false;
        }

        $payload = self::getPayLoad($jwt);
        if (!$payload || !isset($payload['id'])) {
            return false;
        }

        return $payload['id'] == $expectedId;
    }
}
