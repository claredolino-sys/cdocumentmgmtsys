<?php
/**
 * Authentication Middleware
 * Handles JWT token validation and role-based access control
 */

require_once __DIR__ . '/../utils/JWT.php';

class AuthMiddleware {
    
    public static function authenticate() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (empty($authHeader)) {
            http_response_code(401);
            echo json_encode(['error' => 'No authorization token provided']);
            exit();
        }
        
        // Extract token from "Bearer <token>"
        $token = str_replace('Bearer ', '', $authHeader);
        
        try {
            $decoded = JWT::decode($token);
            return $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit();
        }
    }
    
    public static function requireRole($allowedRoles) {
        $user = self::authenticate();
        
        if (!in_array($user->role, $allowedRoles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Insufficient permissions.']);
            exit();
        }
        
        return $user;
    }
}
