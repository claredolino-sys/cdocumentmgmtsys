<?php
/**
 * Authentication Controller
 * Handles user login, logout, and password changes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * User Login
     * Expects: school_id and password
     */
    public function login() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->school_id) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(['error' => 'School ID and password are required']);
            return;
        }
        
        try {
            $query = "SELECT u.*, d.name as department_name 
                      FROM users u 
                      LEFT JOIN departments d ON u.department_id = d.id 
                      WHERE u.school_id = :school_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':school_id', $data->school_id);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($data->password, $user['password_hash'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
                return;
            }
            
            // Log the login activity
            $this->logActivity($user['id'], $user['department_name'] ?? 'Admin Office', 'Login', null);
            
            // Create JWT token
            $token = JWT::encode([
                'user_id' => $user['id'],
                'school_id' => $user['school_id'],
                'role' => $user['role'],
                'department_id' => $user['department_id'],
                'full_name' => $user['full_name']
            ]);
            
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'school_id' => $user['school_id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'department_id' => $user['department_id'],
                    'department_name' => $user['department_name'],
                    'profile_picture_url' => $user['profile_picture_url']
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get Current User Profile
     */
    public function getProfile() {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT u.*, d.name as department_name 
                      FROM users u 
                      LEFT JOIN departments d ON u.department_id = d.id 
                      WHERE u.id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user->user_id);
            $stmt->execute();
            
            $userData = $stmt->fetch();
            
            if (!$userData) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }
            
            unset($userData['password_hash']);
            
            http_response_code(200);
            echo json_encode($userData);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch profile: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Change Password
     */
    public function changePassword() {
        $user = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->old_password) || empty($data->new_password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Old password and new password are required']);
            return;
        }
        
        try {
            // Verify old password
            $query = "SELECT password_hash FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user->user_id);
            $stmt->execute();
            
            $currentUser = $stmt->fetch();
            
            if (!password_verify($data->old_password, $currentUser['password_hash'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Current password is incorrect']);
                return;
            }
            
            // Update password
            $new_password_hash = password_hash($data->new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password_hash = :password_hash WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password_hash', $new_password_hash);
            $stmt->bindParam(':user_id', $user->user_id);
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Password changed successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to change password: ' . $e->getMessage()]);
        }
    }
    
    private function logActivity($user_id, $office, $operation, $record_series = null) {
        try {
            $query = "INSERT INTO activity_logs (user_id, office, operation, record_series_title_description) 
                      VALUES (:user_id, :office, :operation, :record_series)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':office', $office);
            $stmt->bindParam(':operation', $operation);
            $stmt->bindParam(':record_series', $record_series);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}
