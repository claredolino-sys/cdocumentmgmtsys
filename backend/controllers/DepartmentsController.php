<?php
/**
 * Departments Controller
 * Handles department management (Admin only)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DepartmentsController {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all departments
     */
    public function getDepartments() {
        AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT * FROM departments ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $departments = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($departments);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch departments: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create a new department (Admin only)
     */
    public function createDepartment() {
        $user = AuthMiddleware::requireRole(['Admin']);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required']);
            return;
        }
        
        try {
            $query = "INSERT INTO departments (name) VALUES (:name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data->name);
            $stmt->execute();
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Department created successfully',
                'id' => $this->conn->lastInsertId()
            ]);
            
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                http_response_code(409);
                echo json_encode(['error' => 'Department already exists']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create department: ' . $e->getMessage()]);
            }
        }
    }
    
    /**
     * Update a department (Admin only)
     */
    public function updateDepartment($id) {
        $user = AuthMiddleware::requireRole(['Admin']);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required']);
            return;
        }
        
        try {
            $query = "UPDATE departments SET name = :name WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data->name);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Department updated successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update department: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete a department (Admin only)
     */
    public function deleteDepartment($id) {
        $user = AuthMiddleware::requireRole(['Admin']);
        
        try {
            $query = "DELETE FROM departments WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Department deleted successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete department: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get analytics by department
     */
    public function getDepartmentAnalytics() {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT d.name as department, COUNT(r.id) as document_count 
                      FROM departments d 
                      LEFT JOIN records r ON d.id = r.department_id";
            
            // Filter for non-admin users
            if ($user->role !== 'Admin') {
                $query .= " WHERE d.id = :department_id";
            }
            
            $query .= " GROUP BY d.id, d.name ORDER BY d.name ASC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            $analytics = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($analytics);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch analytics: ' . $e->getMessage()]);
        }
    }
}
