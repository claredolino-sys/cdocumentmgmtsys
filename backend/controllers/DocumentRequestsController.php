<?php
/**
 * Document Requests Controller
 * Handles document access requests
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DocumentRequestsController {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all document requests
     * Admin sees all, others see only their own
     */
    public function getRequests() {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT dr.*, r.record_series_title_description, 
                      u.full_name as requester_name, u.email as requester_email,
                      d.name as requester_department,
                      a.full_name as approver_name
                      FROM document_requests dr
                      JOIN records r ON dr.record_id = r.id
                      JOIN users u ON dr.requester_user_id = u.id
                      LEFT JOIN departments d ON u.department_id = d.id
                      LEFT JOIN users a ON dr.approver_user_id = a.id
                      WHERE 1=1";
            
            // Filter based on role
            if ($user->role !== 'Admin') {
                $query .= " AND dr.requester_user_id = :user_id";
            }
            
            $query .= " ORDER BY dr.request_date DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':user_id', $user->user_id);
            }
            
            $stmt->execute();
            $requests = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($requests);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch requests: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create a document request
     */
    public function createRequest() {
        $user = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->record_id) || empty($data->purpose)) {
            http_response_code(400);
            echo json_encode(['error' => 'Record ID and purpose are required']);
            return;
        }
        
        try {
            $query = "INSERT INTO document_requests (record_id, requester_user_id, purpose, id_document_path)
                      VALUES (:record_id, :requester_user_id, :purpose, :id_document_path)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':record_id', $data->record_id);
            $stmt->bindParam(':requester_user_id', $user->user_id);
            $stmt->bindParam(':purpose', $data->purpose);
            $id_path = $data->id_document_path ?? null;
            $stmt->bindParam(':id_document_path', $id_path);
            
            $stmt->execute();
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Request submitted successfully',
                'id' => $this->conn->lastInsertId()
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create request: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Approve or deny a request (Admin only)
     */
    public function updateRequestStatus($id) {
        $user = AuthMiddleware::requireRole(['Admin']);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->status)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status is required']);
            return;
        }
        
        $validStatuses = ['Pending', 'Approved', 'Denied'];
        if (!in_array($data->status, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            return;
        }
        
        try {
            $query = "UPDATE document_requests SET 
                      status = :status,
                      approver_user_id = :approver_user_id,
                      approval_date = CURRENT_TIMESTAMP
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $data->status);
            $stmt->bindParam(':approver_user_id', $user->user_id);
            $stmt->bindParam(':id', $id);
            
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Request updated successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update request: ' . $e->getMessage()]);
        }
    }
}
