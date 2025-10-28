# CDMIS Backend API

This is the backend API for the Centralized Document Management Information System (CDMIS).

## Requirements

- PHP 8.0 or higher
- MySQL/MariaDB 10.4 or higher
- Apache/Nginx web server
- Composer (optional, for production dependencies)

## Setup

1. **Database Configuration**
   - Import the `cdmis_db.sql` file into your MySQL/MariaDB database
   - Update the `.env` file with your database credentials

2. **Environment Configuration**
   - Copy `.env.example` to `.env`
   - Update the configuration values as needed

3. **File Permissions**
   - Ensure the `public/uploads` directory is writable
   ```bash
   chmod -R 755 public/uploads
   ```

4. **Web Server Configuration**
   
   **For Apache:**
   - Ensure mod_rewrite is enabled
   - The `.htaccess` file handles URL rewriting
   
   **For Nginx:**
   Add this to your server configuration:
   ```nginx
   location /backend/ {
       try_files $uri $uri/ /backend/api.php?$query_string;
   }
   ```

5. **PHP Built-in Server (Development Only)**
   ```bash
   php -S localhost:8000
   ```

## API Endpoints

### Authentication
- `POST /auth/login` - User login
- `GET /auth/profile` - Get current user profile
- `POST /auth/change-password` - Change password

### Records
- `GET /records` - Get all records
- `GET /records/{id}` - Get specific record
- `POST /records` - Create new record
- `PUT /records/{id}` - Update record
- `DELETE /records/{id}` - Delete record
- `GET /records/disposal-reminders` - Get records due for disposal
- `GET /records/public` - Get publicly available documents

### Departments
- `GET /departments` - Get all departments
- `POST /departments` - Create department (Admin only)
- `PUT /departments/{id}` - Update department (Admin only)
- `DELETE /departments/{id}` - Delete department (Admin only)
- `GET /departments/analytics` - Get department analytics

### Users
- `GET /users` - Get all users (Admin only)
- `POST /users` - Create user (Admin only)
- `PUT /users/{id}` - Update user (Admin only)
- `DELETE /users/{id}` - Delete user (Admin only)
- `POST /users/{id}/reset-password` - Reset user password (Admin only)

### Document Requests
- `GET /requests` - Get document requests
- `POST /requests` - Create document request
- `PUT /requests/{id}` - Update request status (Admin only)

### Activity Logs
- `GET /activity-logs` - Get activity logs (Admin and Custodian only)

### File Uploads
- `POST /files` - Upload file for a record
- `GET /files/{record_id}` - Get files for a record
- `DELETE /files/{file_id}` - Delete a file

## Authentication

All protected endpoints require a JWT token in the Authorization header:
```
Authorization: Bearer <token>
```

## Security Features

- Password hashing using PHP's password_hash()
- JWT-based authentication
- Role-based access control (RBAC)
- SQL injection prevention using prepared statements
- XSS protection headers
- CORS support

## Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Change `JWT_SECRET` to a secure random string
3. Enable HTTPS
4. Set appropriate file permissions
5. Configure database backup
6. Enable error logging
