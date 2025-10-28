<?php
/**
 * Activity Logs Controller
 * Handles viewing activity logs (audit trail)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ActivityLogsController {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get activity logs
     * Admin sees all, Departmental Record Custodian sees only their department
     */
    public function getLogs() {
        $user = AuthMiddleware::authenticate();
        
        // Staff cannot access activity logs
        if ($user->role === 'Staff') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        try {
            $query = "SELECT al.*, u.full_name as user_name, u.school_id 
                      FROM activity_logs al
                      JOIN users u ON al.user_id = u.id
                      WHERE 1=1";
            
            // Filter by department for Departmental Record Custodian
            if ($user->role === 'Departmental Record Custodian') {
                $query .= " AND u.department_id = :department_id";
            }
            
            $query .= " ORDER BY al.action_date_time DESC LIMIT 1000";
            
            $stmt = $this->conn->prepare($query);
            
            if ($user->role === 'Departmental Record Custodian') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            $logs = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($logs);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch logs: ' . $e->getMessage()]);
        }
    }
}
