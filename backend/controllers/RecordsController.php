<?php
/**
 * Records Controller
 * Handles CRUD operations for document records
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class RecordsController {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all records (filtered by department for non-admin users)
     */
    public function getRecords() {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT r.*, d.name as department_name, u.full_name as created_by_name 
                      FROM records r 
                      LEFT JOIN departments d ON r.department_id = d.id 
                      LEFT JOIN users u ON r.created_by_user_id = u.id 
                      WHERE 1=1";
            
            // Filter by department for non-admin users
            if ($user->role !== 'Admin') {
                $query .= " AND r.department_id = :department_id";
            }
            
            $query .= " ORDER BY r.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            $records = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($records);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch records: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get a single record by ID
     */
    public function getRecord($id) {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT r.*, d.name as department_name, u.full_name as created_by_name 
                      FROM records r 
                      LEFT JOIN departments d ON r.department_id = d.id 
                      LEFT JOIN users u ON r.created_by_user_id = u.id 
                      WHERE r.id = :id";
            
            // Filter by department for non-admin users
            if ($user->role !== 'Admin') {
                $query .= " AND r.department_id = :department_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            $record = $stmt->fetch();
            
            if (!$record) {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found']);
                return;
            }
            
            // Get associated files
            $filesQuery = "SELECT * FROM record_files WHERE record_id = :record_id";
            $filesStmt = $this->conn->prepare($filesQuery);
            $filesStmt->bindParam(':record_id', $id);
            $filesStmt->execute();
            $record['files'] = $filesStmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($record);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch record: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create a new record
     */
    public function createRecord() {
        $user = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents("php://input"));
        
        // Determine department_id based on user role
        $department_id = $user->role === 'Admin' ? $data->department_id : $user->department_id;
        
        try {
            $query = "INSERT INTO records (
                record_series_title_description, period_covered, volume, record_medium,
                restrictions, location, frequency_of_use, duplication, time_value,
                utility_value, retention_period_active, retention_period_storage,
                retention_period_total, disposition_provision, date_of_record,
                department_id, created_by_user_id
            ) VALUES (
                :record_series_title_description, :period_covered, :volume, :record_medium,
                :restrictions, :location, :frequency_of_use, :duplication, :time_value,
                :utility_value, :retention_period_active, :retention_period_storage,
                :retention_period_total, :disposition_provision, :date_of_record,
                :department_id, :created_by_user_id
            )";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':record_series_title_description', $data->record_series_title_description);
            $stmt->bindParam(':period_covered', $data->period_covered);
            $stmt->bindParam(':volume', $data->volume);
            $stmt->bindParam(':record_medium', $data->record_medium);
            $stmt->bindParam(':restrictions', $data->restrictions);
            $stmt->bindParam(':location', $data->location);
            $stmt->bindParam(':frequency_of_use', $data->frequency_of_use);
            $stmt->bindParam(':duplication', $data->duplication);
            $stmt->bindParam(':time_value', $data->time_value);
            $stmt->bindParam(':utility_value', $data->utility_value);
            $stmt->bindParam(':retention_period_active', $data->retention_period_active);
            $stmt->bindParam(':retention_period_storage', $data->retention_period_storage);
            $stmt->bindParam(':retention_period_total', $data->retention_period_total);
            $stmt->bindParam(':disposition_provision', $data->disposition_provision);
            $stmt->bindParam(':date_of_record', $data->date_of_record);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->bindParam(':created_by_user_id', $user->user_id);
            
            $stmt->execute();
            
            http_response_code(201);
            echo json_encode([
                'message' => 'Record created successfully',
                'id' => $this->conn->lastInsertId()
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create record: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update a record
     */
    public function updateRecord($id) {
        $user = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents("php://input"));
        
        try {
            $query = "UPDATE records SET
                record_series_title_description = :record_series_title_description,
                period_covered = :period_covered,
                volume = :volume,
                record_medium = :record_medium,
                restrictions = :restrictions,
                location = :location,
                frequency_of_use = :frequency_of_use,
                duplication = :duplication,
                time_value = :time_value,
                utility_value = :utility_value,
                retention_period_active = :retention_period_active,
                retention_period_storage = :retention_period_storage,
                retention_period_total = :retention_period_total,
                disposition_provision = :disposition_provision,
                date_of_record = :date_of_record
                WHERE id = :id";
            
            // Filter by department for non-admin users
            if ($user->role !== 'Admin') {
                $query .= " AND department_id = :department_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':record_series_title_description', $data->record_series_title_description);
            $stmt->bindParam(':period_covered', $data->period_covered);
            $stmt->bindParam(':volume', $data->volume);
            $stmt->bindParam(':record_medium', $data->record_medium);
            $stmt->bindParam(':restrictions', $data->restrictions);
            $stmt->bindParam(':location', $data->location);
            $stmt->bindParam(':frequency_of_use', $data->frequency_of_use);
            $stmt->bindParam(':duplication', $data->duplication);
            $stmt->bindParam(':time_value', $data->time_value);
            $stmt->bindParam(':utility_value', $data->utility_value);
            $stmt->bindParam(':retention_period_active', $data->retention_period_active);
            $stmt->bindParam(':retention_period_storage', $data->retention_period_storage);
            $stmt->bindParam(':retention_period_total', $data->retention_period_total);
            $stmt->bindParam(':disposition_provision', $data->disposition_provision);
            $stmt->bindParam(':date_of_record', $data->date_of_record);
            $stmt->bindParam(':id', $id);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Record updated successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update record: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete a record
     */
    public function deleteRecord($id) {
        $user = AuthMiddleware::authenticate();
        
        // Only Admin and Departmental Record Custodian can delete
        if (!in_array($user->role, ['Admin', 'Departmental Record Custodian'])) {
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to delete records']);
            return;
        }
        
        try {
            $query = "DELETE FROM records WHERE id = :id";
            
            // Filter by department for non-admin users
            if ($user->role !== 'Admin') {
                $query .= " AND department_id = :department_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            
            http_response_code(200);
            echo json_encode(['message' => 'Record deleted successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete record: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get records flagged for disposal
     */
    public function getDisposalReminders() {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT r.*, d.name as department_name 
                      FROM records r 
                      LEFT JOIN departments d ON r.department_id = d.id 
                      WHERE r.calculated_disposal_date IS NOT NULL 
                      AND r.calculated_disposal_date <= CURDATE()";
            
            // Filter by department for non-admin users
            if ($user->role !== 'Admin') {
                $query .= " AND r.department_id = :department_id";
            }
            
            $query .= " ORDER BY r.calculated_disposal_date ASC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($user->role !== 'Admin') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            $records = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($records);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch disposal reminders: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get publicly available documents
     */
    public function getPublicDocuments() {
        $user = AuthMiddleware::authenticate();
        
        try {
            $query = "SELECT * FROM publicly_available_documents WHERE 1=1";
            
            // Staff can only see their department's documents
            if ($user->role === 'Staff') {
                $query .= " AND department_id = :department_id";
            }
            // Departmental Record Custodian sees all public documents
            // Admin sees all public documents
            
            $stmt = $this->conn->prepare($query);
            
            if ($user->role === 'Staff') {
                $stmt->bindParam(':department_id', $user->department_id);
            }
            
            $stmt->execute();
            $documents = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode($documents);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch public documents: ' . $e->getMessage()]);
        }
    }
}
