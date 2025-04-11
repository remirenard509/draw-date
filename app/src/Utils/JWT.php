<?php

namespace App\Utils;

class JWT {
  private static $secret;

  public function __construct() {
    self::$secret = getenv('JWT_SECRET');
  }

  public static function generate($payload) {
    // Base 64
      // Header
    $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    // Payload
    $payload = self::base64UrlEncode(json_encode($payload));
    
    // Concaténation header . payload
    $concat_signature = "$header.$payload";
    // Génération de la signature avec hash
    $signature = hash_hmac("sha256", $concat_signature, self::$secret, true);
      //  base64 de la signature
    $signature = self::base64UrlEncode($signature);

    // Return -> header . payload . signature
    return "$header.$payload.$signature";
  }

  public static function verify($jwt) {

    $segments = explode('.', $jwt);
    if (count($segments) !== 3) {
        return false;
    }

    list($header, $payload, $signature) = $segments;
    $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", self::$secret, true));
    
    return hash_equals($expectedSignature, $signature);
 }
  
  private static function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  public static function getPayLoad($jwt) {
    $segments = explode('.', $jwt);
    if (count($segments) !== 3) {
        return null;
    }

    list(, $payload) = $segments;
    return json_decode(base64_decode($payload), true);
  }
  public static function isExpired($jwt) {
    $payload = self::getPayLoad($jwt);
    if ($payload && isset($payload['exp'])) {
        return $payload['exp'] < time();
    }
    return true;
  }
}