# CDMIS API Testing Guide

This guide provides example API calls you can use to test the CDMIS backend.

## Base URL
```
http://localhost:8000
```

## Authentication

All protected endpoints require a JWT token in the Authorization header:
```
Authorization: Bearer <your_jwt_token>
```

---

## 1. Authentication Endpoints

### Login
**POST** `/auth/login`

Request Body:
```json
{
  "school_id": "00-0-00001",
  "password": "1234"
}
```

Response (200 OK):
```json
{
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "school_id": "00-0-00001",
    "full_name": "System Admin",
    "email": "admin@bipsu.edu.ph",
    "role": "Admin",
    "department_id": null,
    "department_name": null,
    "profile_picture_url": null
  }
}
```

### Get Profile
**GET** `/auth/profile`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
{
  "id": 1,
  "school_id": "00-0-00001",
  "full_name": "System Admin",
  "email": "admin@bipsu.edu.ph",
  "role": "Admin",
  "department_id": null,
  "department_name": null,
  "profile_picture_url": null,
  "created_at": "2025-10-28 10:00:00",
  "updated_at": "2025-10-28 10:00:00"
}
```

### Change Password
**POST** `/auth/change-password`

Headers:
```
Authorization: Bearer <token>
```

Request Body:
```json
{
  "old_password": "1234",
  "new_password": "5678"
}
```

Response (200 OK):
```json
{
  "message": "Password changed successfully"
}
```

---

## 2. Records Endpoints

### Get All Records
**GET** `/records`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "record_series_title_description": "Student Academic Records",
    "period_covered": "2023-2024",
    "volume": "10 cubic feet",
    "record_medium": "Paper",
    "restrictions": "Publicly Available",
    "location": "Records Room A",
    "frequency_of_use": "Frequent",
    "duplication": "None",
    "time_value": "P",
    "utility_value": "Arc",
    "retention_period_active": "5 years",
    "retention_period_storage": "10 years",
    "retention_period_total": "15 years",
    "disposition_provision": "1",
    "date_of_record": "2023-01-01",
    "calculated_disposal_date": null,
    "department_id": 1,
    "department_name": "Registrar's Office",
    "created_by_user_id": 1,
    "created_by_name": "System Admin",
    "created_at": "2025-10-28 10:00:00",
    "updated_at": "2025-10-28 10:00:00"
  }
]
```

### Get Single Record
**GET** `/records/{id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
{
  "id": 1,
  "record_series_title_description": "Student Academic Records",
  "period_covered": "2023-2024",
  // ... other fields ...
  "files": [
    {
      "id": 1,
      "record_id": 1,
      "file_name": "document.pdf",
      "file_path": "abc123_1234567890.pdf",
      "file_size": 1024000,
      "upload_date": "2025-10-28 10:00:00",
      "uploaded_by_user_id": 1,
      "uploaded_by_name": "System Admin"
    }
  ]
}
```

### Create Record
**POST** `/records`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "record_series_title_description": "Financial Reports 2024",
  "period_covered": "2024",
  "volume": "5 cubic feet",
  "record_medium": "Digital",
  "restrictions": "Confidential",
  "location": "Server Room",
  "frequency_of_use": "Occasional",
  "duplication": "Backup",
  "time_value": "T",
  "utility_value": "F",
  "retention_period_active": "3 years",
  "retention_period_storage": "7 years",
  "retention_period_total": "10 years",
  "disposition_provision": "5",
  "date_of_record": "2024-01-01",
  "department_id": 2
}
```

Response (201 Created):
```json
{
  "message": "Record created successfully",
  "id": 2
}
```

### Update Record
**PUT** `/records/{id}`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body: (same as create, partial updates allowed)

Response (200 OK):
```json
{
  "message": "Record updated successfully"
}
```

### Delete Record
**DELETE** `/records/{id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
{
  "message": "Record deleted successfully"
}
```

### Get Disposal Reminders
**GET** `/records/disposal-reminders`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 5,
    "record_series_title_description": "Old Financial Reports",
    "calculated_disposal_date": "2025-01-15",
    "department_name": "Finance Office",
    // ... other fields ...
  }
]
```

### Get Public Documents
**GET** `/records/public`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "record_series_title_description": "University Policies",
    "period_covered": "2024",
    "department_name": "President's Office",
    "department_id": 1
  }
]
```

---

## 3. Departments Endpoints

### Get All Departments
**GET** `/departments`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "name": "Registrar's Office",
    "created_at": "2025-10-28 10:00:00",
    "updated_at": "2025-10-28 10:00:00"
  },
  {
    "id": 2,
    "name": "Finance Office",
    "created_at": "2025-10-28 10:00:00",
    "updated_at": "2025-10-28 10:00:00"
  }
]
```

### Create Department (Admin Only)
**POST** `/departments`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "name": "Human Resources Office"
}
```

Response (201 Created):
```json
{
  "message": "Department created successfully",
  "id": 3
}
```

### Update Department (Admin Only)
**PUT** `/departments/{id}`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "name": "Human Resources Department"
}
```

Response (200 OK):
```json
{
  "message": "Department updated successfully"
}
```

### Delete Department (Admin Only)
**DELETE** `/departments/{id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
{
  "message": "Department deleted successfully"
}
```

### Get Department Analytics
**GET** `/departments/analytics`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "department": "Registrar's Office",
    "document_count": 150
  },
  {
    "department": "Finance Office",
    "document_count": 89
  },
  {
    "department": "Human Resources",
    "document_count": 45
  }
]
```

---

## 4. Users Endpoints (Admin Only)

### Get All Users
**GET** `/users`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "school_id": "00-0-00001",
    "full_name": "System Admin",
    "email": "admin@bipsu.edu.ph",
    "role": "Admin",
    "department_id": null,
    "department_name": null,
    "created_at": "2025-10-28 10:00:00"
  },
  {
    "id": 2,
    "school_id": "22-1-12345",
    "full_name": "John Doe",
    "email": "john.doe@bipsu.edu.ph",
    "role": "Departmental Record Custodian",
    "department_id": 1,
    "department_name": "Registrar's Office",
    "created_at": "2025-10-28 11:00:00"
  }
]
```

### Create User
**POST** `/users`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "school_id": "22-1-54321",
  "password": "1234",
  "full_name": "Jane Smith",
  "email": "jane.smith@bipsu.edu.ph",
  "role": "Staff",
  "department_id": 2
}
```

Response (201 Created):
```json
{
  "message": "User created successfully",
  "id": 3
}
```

### Update User
**PUT** `/users/{id}`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "full_name": "Jane M. Smith",
  "email": "jsmith@bipsu.edu.ph",
  "role": "Departmental Record Custodian",
  "department_id": 2
}
```

Response (200 OK):
```json
{
  "message": "User updated successfully"
}
```

### Delete User
**DELETE** `/users/{id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
{
  "message": "User deleted successfully"
}
```

### Reset User Password
**POST** `/users/{id}/reset-password`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "new_password": "5678"
}
```

Response (200 OK):
```json
{
  "message": "Password reset successfully"
}
```

---

## 5. Document Requests Endpoints

### Get All Requests
**GET** `/requests`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "record_id": 5,
    "requester_user_id": 3,
    "purpose": "Research purposes",
    "id_document_path": null,
    "status": "Pending",
    "request_date": "2025-10-28 12:00:00",
    "approval_date": null,
    "approver_user_id": null,
    "record_series_title_description": "Historical Documents",
    "requester_name": "Jane Smith",
    "requester_email": "jane.smith@bipsu.edu.ph",
    "requester_department": "History Department",
    "approver_name": null
  }
]
```

### Create Request
**POST** `/requests`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "record_id": 5,
  "purpose": "For academic research on university history",
  "id_document_path": null
}
```

Response (201 Created):
```json
{
  "message": "Request submitted successfully",
  "id": 2
}
```

### Update Request Status (Admin Only)
**PUT** `/requests/{id}`

Headers:
```
Authorization: Bearer <token>
Content-Type: application/json
```

Request Body:
```json
{
  "status": "Approved"
}
```

Response (200 OK):
```json
{
  "message": "Request updated successfully"
}
```

---

## 6. Activity Logs Endpoints

### Get Activity Logs
**GET** `/activity-logs`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "user_id": 1,
    "office": "Admin Office",
    "operation": "Login",
    "action_date_time": "2025-10-28 10:00:00",
    "record_series_title_description": null,
    "details": null,
    "user_name": "System Admin",
    "school_id": "00-0-00001"
  },
  {
    "id": 2,
    "user_id": 2,
    "office": "Registrar's Office",
    "operation": "Upload",
    "action_date_time": "2025-10-28 11:30:00",
    "record_series_title_description": "Student Records",
    "details": "File: students_2024.pdf",
    "user_name": "John Doe",
    "school_id": "22-1-12345"
  }
]
```

---

## 7. File Upload Endpoints

### Upload File
**POST** `/files`

Headers:
```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

Form Data:
```
file: <select file>
record_id: 1
```

Response (201 Created):
```json
{
  "message": "File uploaded successfully",
  "file_id": 1,
  "filename": "abc123_1234567890.pdf"
}
```

### Get Files for Record
**GET** `/files/{record_id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
[
  {
    "id": 1,
    "record_id": 1,
    "file_name": "document.pdf",
    "file_path": "abc123_1234567890.pdf",
    "file_size": 1024000,
    "upload_date": "2025-10-28 10:00:00",
    "uploaded_by_user_id": 1,
    "uploaded_by_name": "System Admin"
  }
]
```

### Delete File
**DELETE** `/files/{file_id}`

Headers:
```
Authorization: Bearer <token>
```

Response (200 OK):
```json
{
  "message": "File deleted successfully"
}
```

---

## Testing with cURL

### Login Example
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"school_id":"00-0-00001","password":"1234"}'
```

### Get Records Example
```bash
curl -X GET http://localhost:8000/records \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Create Department Example
```bash
curl -X POST http://localhost:8000/departments \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"name":"New Department"}'
```

---

## Common Error Responses

### 400 Bad Request
```json
{
  "error": "School ID and password are required"
}
```

### 401 Unauthorized
```json
{
  "error": "Invalid credentials"
}
```

or

```json
{
  "error": "Invalid or expired token"
}
```

### 403 Forbidden
```json
{
  "error": "Access denied. Insufficient permissions."
}
```

### 404 Not Found
```json
{
  "error": "Record not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error: <error message>"
}
```

---

## Notes

1. **Token Expiration**: JWT tokens expire after 24 hours by default
2. **File Size Limit**: Maximum file upload size is 10MB
3. **Password Length**: Passwords must be exactly 4 characters
4. **Role Access**: 
   - Admin: Full access to all endpoints
   - Departmental Record Custodian: Department-specific access
   - Staff: Limited read access only
5. **Database**: Ensure `cdmis_db` database is created and seeded before testing

---

## Quick Test Sequence

1. **Start Backend**: `cd backend && php -S localhost:8000`
2. **Login**: Call `/auth/login` to get token
3. **Get Departments**: Call `/departments` with token
4. **Create Record**: Call POST `/records` with token
5. **Get Records**: Call `/records` to see your new record
6. **View Analytics**: Call `/departments/analytics`
7. **Check Logs**: Call `/activity-logs` to see logged activities
