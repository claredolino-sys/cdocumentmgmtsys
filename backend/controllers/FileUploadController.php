<?php
/**
 * File Upload Controller
 * Handles document file uploads
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class FileUploadController {
    private $db;
    private $conn;
    private $uploadDir;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->uploadDir = __DIR__ . '/../public/uploads/documents/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload a file for a record
     */
    public function uploadFile() {
        $user = AuthMiddleware::authenticate();
        
        if (!isset($_FILES['file']) || !isset($_POST['record_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'File and record ID are required']);
            return;
        }
        
        $record_id = $_POST['record_id'];
        $file = $_FILES['file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'File upload failed']);
            return;
        }
        
        // Check file size (10MB limit)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['error' => 'File size exceeds 10MB limit']);
            return;
        }
        
        try {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $this->uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Save file info to database
            $query = "INSERT INTO record_files (record_id, file_name, file_path, file_size, uploaded_by_user_id)
                      VALUES (:record_id, :file_name, :file_path, :file_size, :uploaded_by_user_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':record_id', $record_id);
            $stmt->bindParam(':file_name', $file['name']);
            $stmt->bindParam(':file_path', $filename);
            $stmt->bindParam(':file_size', $file['size']);
            $stmt->bindParam(':uploaded_by_user_id', $user->user_id);
            $stmt->execute();
            
            // Log activity
            $this->logActivity($user->user_id, 'Upload', "File: " . $file['name']);
            
            http_response_code(201);
            echo json_encode([
                'message' => 'File uploaded successfully',
                'file_id' => $this->conn->lastInsertId(),
                'filename' => $filename
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to upload file: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get files for a record
     */
    public function getFiles($record_id) {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT rf.*, u.full_name as uploaded_by_name
                      FROM record_files rf
                      JOIN users u ON rf.uploaded_by_user_id = u.id
                      WHERE rf.record_id = :record_id
                      ORDER BY rf.upload_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':record_id', $record_id);
            $stmt->execute();
            $files = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($files);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch files: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete a file
     */
    public function deleteFile($file_id) {
        $user = AuthMiddleware::authenticate();
        
        try {
            // Get file info
            $query = "SELECT * FROM record_files WHERE id = :file_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':file_id', $file_id);
            $stmt->execute();
            $file = $stmt->fetch();
            
            if (!$file) {
                http_response_code(404);
                echo json_encode(['error' => 'File not found']);
                return;
            }
            
            // Delete from database
            $query = "DELETE FROM record_files WHERE id = :file_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':file_id', $file_id);
            $stmt->execute();
            
            // Delete physical file
            $filepath = $this->uploadDir . $file['file_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            http_response_code(200);
            echo json_encode(['message' => 'File deleted successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete file: ' . $e->getMessage()]);
        }
    }
    
    private function logActivity($user_id, $operation, $details) {
        try {
            $query = "SELECT d.name FROM users u 
                      LEFT JOIN departments d ON u.department_id = d.id 
                      WHERE u.id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch();
            $office = $result['name'] ?? 'Admin Office';
            
            $query = "INSERT INTO activity_logs (user_id, office, operation, details) 
                      VALUES (:user_id, :office, :operation, :details)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':office', $office);
            $stmt->bindParam(':operation', $operation);
            $stmt->bindParam(':details', $details);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}
