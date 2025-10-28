# Centralized Document Management Information System (CDMIS)

A comprehensive web-based document management system for Biliran Province State University.

## 🚀 Project Overview

CDMIS is a full-stack web application designed to manage document inventories, track user activity, handle document requests, and automate reminders for record disposal based on predefined schedules.

## 📥 Clone Repository

To clone this repository and use it for your web app (including GitHub Spark):

```bash
git clone https://github.com/claredolino-sys/cdocumentmgmtsys.git
cd cdocumentmgmtsys
```

## 📋 Features

### User Roles
- **Admin**: Full system access, manages all documents, users, and settings
- **Departmental Record Custodian**: Manages documents for assigned department
- **Staff**: Limited access, can view and request publicly available documents

### Core Functionalities
- ✅ Role-based authentication and authorization
- ✅ Document inventory management with Excel-style grid
- ✅ File upload and document attachment
- ✅ Record disposal automation with filtration engine
- ✅ Activity logging and audit trails
- ✅ Document request workflow
- ✅ Analytics and reporting with charts
- ✅ Department and user management
- ✅ Publicly available document filtering

## 🛠️ Technology Stack

### Frontend
- **Framework**: React 18 with TypeScript
- **Build Tool**: Vite
- **Routing**: React Router DOM
- **HTTP Client**: Axios
- **UI Components**: Radix UI
- **Styling**: Tailwind CSS
- **Charts**: Recharts
- **Tables**: TanStack Table

### Backend
- **Language**: PHP 8.3+
- **Database**: MySQL/MariaDB
- **Authentication**: JWT (JSON Web Tokens)
- **Architecture**: RESTful API

## 📁 Project Structure

```
cdocumentmgmtsys/
├── backend/                     # PHP REST API
│   ├── config/                  # Database configuration
│   ├── controllers/             # API controllers
│   ├── middleware/              # Authentication middleware
│   ├── models/                  # Data models (future)
│   ├── routes/                  # API routes (future)
│   ├── utils/                   # Utility functions (JWT)
│   ├── public/uploads/          # File uploads directory
│   ├── api.php                  # Main API entry point
│   ├── .env.example             # Environment configuration template
│   └── README.md                # Backend documentation
│
├── CDMIS LOG IN PAGE/           # React Frontend
│   ├── src/
│   │   ├── config/              # API configuration
│   │   ├── contexts/            # React contexts (Auth)
│   │   ├── services/            # API service layer
│   │   ├── types/               # TypeScript types
│   │   ├── components/          # React components
│   │   ├── pages/               # Page components
│   │   ├── hooks/               # Custom hooks
│   │   └── utils/               # Utility functions
│   ├── package.json
│   ├── .env.example
│   └── IMPLEMENTATION_GUIDE.md  # Frontend implementation details
│
├── cdmis_db.sql                 # Database schema
├── CDMIS Inventory Form.pdf     # Inventory form template
└── GENERAL RECORDS DISPOSITION SCHEDULE.docx
```

## 🚀 Getting Started

### Prerequisites
- PHP 8.0 or higher
- MySQL/MariaDB 10.4 or higher
- Node.js 18+ and npm
- Composer (optional)
- Apache/Nginx web server (for production)

### Backend Setup

1. **Database Setup**
   ```bash
   # Import the database
   mysql -u root -p < cdmis_db.sql
   ```

2. **Configure Backend**
   ```bash
   cd backend
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Set Permissions**
   ```bash
   chmod -R 755 public/uploads
   ```

4. **Start PHP Server (Development)**
   ```bash
   php -S localhost:8000
   ```

### Frontend Setup

1. **Install Dependencies**
   ```bash
   cd "CDMIS LOG IN PAGE"
   npm install
   ```

2. **Configure Frontend**
   ```bash
   cp .env.example .env
   # Edit .env if needed (default: http://localhost:8000)
   ```

3. **Start Development Server**
   ```bash
   npm run dev
   ```

4. **Access Application**
   - Open browser to `http://localhost:3000`

### Default Users

After importing the database, you'll need to create users through the Admin interface or directly in the database. 

**Example SQL to create an admin user:**
```sql
INSERT INTO users (school_id, password_hash, full_name, email, role, department_id) 
VALUES (
  '00-0-00001',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "1234"
  'System Admin',
  'admin@bipsu.edu.ph',
  'Admin',
  NULL
);
```

## 📊 Database Schema

The system uses the following main tables:
- `users`: User accounts and authentication
- `departments`: Organizational departments
- `records`: Document inventory
- `record_files`: Uploaded document files
- `document_requests`: Document access requests
- `activity_logs`: Audit trail
- `disposition_schedule`: Record retention schedules

View: `publicly_available_documents` - Filters records marked as publicly available

## 🔐 API Documentation

### Authentication Endpoints
```
POST /auth/login              # User login
GET  /auth/profile            # Get current user profile
POST /auth/change-password    # Change password
```

### Records Endpoints
```
GET    /records                        # Get all records
GET    /records/{id}                   # Get specific record
POST   /records                        # Create record
PUT    /records/{id}                   # Update record
DELETE /records/{id}                   # Delete record
GET    /records/disposal-reminders     # Get disposal reminders
GET    /records/public                 # Get public documents
```

### Department Endpoints
```
GET    /departments             # Get all departments
POST   /departments             # Create department (Admin)
PUT    /departments/{id}        # Update department (Admin)
DELETE /departments/{id}        # Delete department (Admin)
GET    /departments/analytics   # Get analytics
```

### User Endpoints (Admin Only)
```
GET    /users                           # Get all users
POST   /users                           # Create user
PUT    /users/{id}                      # Update user
DELETE /users/{id}                      # Delete user
POST   /users/{id}/reset-password       # Reset password
```

### Request Endpoints
```
GET  /requests          # Get document requests
POST /requests          # Create request
PUT  /requests/{id}     # Update request status (Admin)
```

### File Endpoints
```
POST   /files              # Upload file
GET    /files/{record_id}  # Get files for record
DELETE /files/{file_id}    # Delete file
```

### Activity Log Endpoints
```
GET /activity-logs    # Get activity logs
```

All protected endpoints require:
```
Authorization: Bearer <JWT_TOKEN>
```

## 🎨 UI/UX Design

### Color Palette
- **Background**: White (#FFFFFF)
- **Navigation**: Dark Pastel Blue (#4A5568)
- **Accent**: Indigo (#6366F1)
- **Text**: Dark Gray (#1F2937)

### Layout
- Static top navigation panel
- Collapsible side navigation panel
- Responsive design for desktop and tablet

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- JWT-based authentication
- Role-based access control (RBAC)
- SQL injection prevention (prepared statements)
- XSS protection headers
- CORS support
- File upload validation
- Activity logging for audit trails

## 🚀 Deployment

### Production Deployment

1. **Database**
   - Set up MySQL database
   - Import `cdmis_db.sql`
   - Create database user with appropriate permissions

2. **Backend**
   - Upload backend files to web server
   - Configure `.env` with production settings
   - Set `APP_ENV=production`
   - Change `JWT_SECRET` to secure random string
   - Configure Apache/Nginx virtual host
   - Enable HTTPS

3. **Frontend**
   - Build production version: `npm run build`
   - Upload `build/` directory to server
   - Configure web server to serve static files
   - Update API URL in production `.env`

### Google Cloud Deployment

1. **Cloud SQL**
   - Create MySQL instance
   - Import database schema

2. **App Engine / Compute Engine**
   - Deploy PHP backend
   - Configure environment variables

3. **Cloud Storage / App Engine**
   - Deploy React frontend build

4. **Additional Services**
   - Cloud CDN for static assets
   - Cloud Armor for security
   - Cloud Load Balancing

## 📝 License

This project is developed for Biliran Province State University.

## 👥 Support

For issues and questions, please contact the Records and Archives Office (RAO) at Biliran Province State University.

## 🔄 Version

Current Version: 1.0.0

## 📚 Additional Documentation

- [Backend API Documentation](backend/README.md)
- [Frontend Implementation Guide](CDMIS%20LOG%20IN%20PAGE/IMPLEMENTATION_GUIDE.md)
- [Database Schema](cdmis_db.sql)

---

**Developed for Biliran Province State University**
*Centralized Document Management Information System (CDMIS)*
