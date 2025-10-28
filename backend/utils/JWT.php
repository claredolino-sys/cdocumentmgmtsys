<?php
/**
 * Simple JWT Implementation
 * For production, consider using a library like firebase/php-jwt
 */

class JWT {
    private static $secret;
    
    private static function getSecret() {
        if (self::$secret === null) {
            $envSecret = getenv('JWT_SECRET');
            if ($envSecret === false || empty($envSecret)) {
                throw new Exception('JWT_SECRET environment variable is not set');
            }
            self::$secret = $envSecret;
        }
        return self::$secret;
    }
    
    public static function encode($payload, $expiresIn = 86400) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiresIn;
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        $header = base64_decode(self::base64UrlDecode($tokenParts[0]));
        $payload = base64_decode(self::base64UrlDecode($tokenParts[1]));
        $signatureProvided = $tokenParts[2];
        
        $base64UrlHeader = $tokenParts[0];
        $base64UrlPayload = $tokenParts[1];
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        if ($base64UrlSignature !== $signatureProvided) {
            throw new Exception('Invalid token signature');
        }
        
        $payloadData = json_decode($payload);
        
        if ($payloadData->exp < time()) {
            throw new Exception('Token expired');
        }
        
        return $payloadData;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return strtr($data, '-_', '+/');
    }
}
