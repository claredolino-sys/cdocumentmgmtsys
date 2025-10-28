<?php
/**
 * CDMIS API Router
 * Main entry point for all API requests
 */

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Include controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/RecordsController.php';
require_once __DIR__ . '/controllers/DepartmentsController.php';
require_once __DIR__ . '/controllers/UsersController.php';
require_once __DIR__ . '/controllers/DocumentRequestsController.php';
require_once __DIR__ . '/controllers/ActivityLogsController.php';
require_once __DIR__ . '/controllers/FileUploadController.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/backend/api.php', '', $path);
$path = trim($path, '/');
$segments = explode('/', $path);

try {
    // Route the request
    switch ($segments[0]) {
        case 'auth':
            $controller = new AuthController();
            if ($segments[1] === 'login' && $method === 'POST') {
                $controller->login();
            } elseif ($segments[1] === 'profile' && $method === 'GET') {
                $controller->getProfile();
            } elseif ($segments[1] === 'change-password' && $method === 'POST') {
                $controller->changePassword();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'records':
            $controller = new RecordsController();
            if ($method === 'GET' && !isset($segments[1])) {
                $controller->getRecords();
            } elseif ($method === 'GET' && $segments[1] === 'disposal-reminders') {
                $controller->getDisposalReminders();
            } elseif ($method === 'GET' && $segments[1] === 'public') {
                $controller->getPublicDocuments();
            } elseif ($method === 'GET' && isset($segments[1]) && is_numeric($segments[1])) {
                $controller->getRecord($segments[1]);
            } elseif ($method === 'POST') {
                $controller->createRecord();
            } elseif ($method === 'PUT' && isset($segments[1])) {
                $controller->updateRecord($segments[1]);
            } elseif ($method === 'DELETE' && isset($segments[1])) {
                $controller->deleteRecord($segments[1]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'departments':
            $controller = new DepartmentsController();
            if ($method === 'GET' && !isset($segments[1])) {
                $controller->getDepartments();
            } elseif ($method === 'GET' && $segments[1] === 'analytics') {
                $controller->getDepartmentAnalytics();
            } elseif ($method === 'POST') {
                $controller->createDepartment();
            } elseif ($method === 'PUT' && isset($segments[1])) {
                $controller->updateDepartment($segments[1]);
            } elseif ($method === 'DELETE' && isset($segments[1])) {
                $controller->deleteDepartment($segments[1]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'users':
            $controller = new UsersController();
            if ($method === 'GET') {
                $controller->getUsers();
            } elseif ($method === 'POST') {
                $controller->createUser();
            } elseif ($method === 'PUT' && isset($segments[1])) {
                $controller->updateUser($segments[1]);
            } elseif ($method === 'DELETE' && isset($segments[1])) {
                $controller->deleteUser($segments[1]);
            } elseif ($method === 'POST' && isset($segments[1]) && $segments[2] === 'reset-password') {
                $controller->resetPassword($segments[1]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'requests':
            $controller = new DocumentRequestsController();
            if ($method === 'GET') {
                $controller->getRequests();
            } elseif ($method === 'POST') {
                $controller->createRequest();
            } elseif ($method === 'PUT' && isset($segments[1])) {
                $controller->updateRequestStatus($segments[1]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'activity-logs':
            $controller = new ActivityLogsController();
            if ($method === 'GET') {
                $controller->getLogs();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'files':
            $controller = new FileUploadController();
            if ($method === 'POST') {
                $controller->uploadFile();
            } elseif ($method === 'GET' && isset($segments[1])) {
                $controller->getFiles($segments[1]);
            } elseif ($method === 'DELETE' && isset($segments[1])) {
                $controller->deleteFile($segments[1]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
