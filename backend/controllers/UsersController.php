<?php
/**
 * Users Controller
 * Handles user management (Admin only)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UsersController {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all users (Admin only)
     */
    public function getUsers() {
        $user = AuthMiddleware::requireRole(['Admin']);
        
        try {
            $query = "SELECT u.id, u.school_id, u.full_name, u.email, u.role, 
                      u.department_id, d.name as department_name, u.created_at
                      FROM users u 
                      LEFT JOIN departments d ON u.department_id = d.id 
                      ORDER BY u.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($users);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch users: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create a new user (Admin only)
     */
    public function createUser() {
        $user = AuthMiddleware::requireRole(['Admin']);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->school_id) || empty($data->password) || empty($data->email) || empty($data->role)) {
            http_response_code(400);
            echo json_encode(['error' => 'School ID, password, email, and role are required']);
            return;
        }
        
        // Validate role
        $validRoles = ['Admin', 'Departmental Record Custodian', 'Staff'];
        if (!in_array($data->role, $validRoles)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role']);
            return;
        }
        
        // Ensure password is 4 characters
        if (strlen($data->password) !== 4) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be exactly 4 characters']);
            return;
        }
        
        try {
            $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (school_id, password_hash, full_name, email, role, department_id) 
                      VALUES (:school_id, :password_hash, :full_name, :email, :role, :department_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':school_id', $data->school_id);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':full_name', $data->full_name);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':role', $data->role);
            $dept_id = $data->department_id ?? null;
            $stmt->bindParam(':department_id', $dept_id);
            
            $stmt->execute();
            
            http_response_code(201);
            echo json_encode([
                'message' => 'User created successfully',
                'id' => $this->conn->lastInsertId()
            ]);
            
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                http_response_code(409);
                echo json_encode(['error' => 'User with this school ID or email already exists']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create user: ' . $e->getMessage()]);
            }
        }
    }
    
    /**
     * Update a user (Admin only)
     */
    public function updateUser($id) {
        $user = AuthMiddleware::requireRole(['Admin']);
        $data = json_decode(file_get_contents("php://input"));
        
        try {
            $query = "UPDATE users SET 
                      full_name = :full_name,
                      email = :email,
                      role = :role,
                      department_id = :department_id
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':full_name', $data->full_name);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':role', $data->role);
            $dept_id = $data->department_id ?? null;
            $stmt->bindParam(':department_id', $dept_id);
            $stmt->bindParam(':id', $id);
            
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'User updated successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete a user (Admin only)
     */
    public function deleteUser($id) {
        $user = AuthMiddleware::requireRole(['Admin']);
        
        try {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Reset user password (Admin only)
     */
    public function resetPassword($id) {
        $user = AuthMiddleware::requireRole(['Admin']);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->new_password)) {
            http_response_code(400);
            echo json_encode(['error' => 'New password is required']);
            return;
        }
        
        // Ensure password is 4 characters
        if (strlen($data->new_password) !== 4) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be exactly 4 characters']);
            return;
        }
        
        try {
            $password_hash = password_hash($data->new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Password reset successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to reset password: ' . $e->getMessage()]);
        }
    }
}
