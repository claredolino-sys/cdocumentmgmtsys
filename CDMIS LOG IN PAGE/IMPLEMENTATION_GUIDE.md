# CDMIS Frontend Implementation Guide

## Overview
This is a comprehensive React + TypeScript frontend for the Centralized Document Management Information System (CDMIS).

## Project Structure

```
src/
├── config/              # Configuration files (API endpoints, constants)
├── contexts/            # React contexts (Auth, Theme)
├── services/            # API service layer
├── types/               # TypeScript type definitions
├── hooks/               # Custom React hooks
├── components/          # Reusable UI components
│   ├── ui/             # Base UI components (from existing)
│   ├── layout/         # Layout components (Header, Sidebar, etc.)
│   └── common/         # Common components (Tables, Charts, etc.)
├── pages/              # Page components
│   ├── common/         # Login pages
│   ├── admin/          # Admin dashboard pages
│   ├── custodian/      # Custodian dashboard pages
│   └── staff/          # Staff dashboard pages
└── utils/              # Utility functions
```

## Key Features Implemented

### Backend API (✅ Complete)
- PHP REST API with JWT authentication
- Role-based access control (RBAC)
- All CRUD operations for records, users, departments
- File upload handling
- Activity logging
- Document request management
- Disposal reminder system

### Frontend Structure (🚧 In Progress)
- API service layer with Axios
- Authentication context
- Type definitions
- Base application routing structure

## Pages to Implement

### Admin Dashboard
1. **Reports** (Main Page)
   - Pie chart: Documents by Office/Department
   - Record Disposal Reminder list
   
2. **Inventory of Documents**
   - Excel-style grid matching the CDMIS inventory form
   - File upload button for each row
   - Connected to System Inventory & Administration

3. **System Inventory & Administration**
   - Consolidated view of all department inventories
   - Download functionality
   - Connected to Inventory of Documents

4. **Activity Log (Audit Trail)**
   - Table with: Operation, Action Date & Time, Records Series Title, User ID, Office
   - System-wide log of all actions

5. **Request Notifications**
   - List of all document requests
   - Approve/Deny functionality

6. **Manage Departments**
   - Add/Edit/Delete departments

7. **Manage Users**
   - Add/Edit/Delete users
   - Set School ID, Password, Email, Department

8. **Profile**
   - View profile information
   - Change password

### Departmental Record Custodian Dashboard
1. **Reports** (Main Page)
   - Pie chart for their department only
   - Record Disposal Reminder for their department

2. **Inventory of Documents**
   - Same as Admin but filtered to their department

3. **Activity Log**
   - Department-specific audit trail

4. **Document List**
   - Publicly available documents from other departments
   - Request Document button

5. **Profile**
   - View and edit profile
   - Change password

### Staff Dashboard
1. **Document List** (Main Page)
   - Publicly available documents from their department
   - Request Document button

2. **Profile**
   - View profile
   - Change password

## UI/UX Specifications

### Color Scheme
- **Primary Background**: White (#FFFFFF)
- **Navigation (Top & Side)**: Dark Pastel Blue (#4A5568 or similar)
- **Accent**: Indigo (#6366F1)
- **Text**: Dark Gray (#1F2937)

### Layout
- **Top Panel**: Static, contains logo, app name, user profile dropdown
- **Side Panel**: Collapsible navigation menu
- **Main Content**: Full-screen view when side panel is collapsed

### Components Needed
1. **Navigation**
   - TopNav component
   - Sidebar component with collapse functionality
   
2. **Data Display**
   - DataTable component (Excel-style grid)
   - PieChart component (using Recharts)
   - DisposalReminderList component
   
3. **Forms**
   - InventoryForm component
   - DocumentRequestForm component
   - UserForm component
   - DepartmentForm component
   - PasswordChangeForm component

4. **Modals**
   - RequestDocumentModal
   - ConfirmationDialog

## Environment Configuration

Create a `.env` file:
```env
VITE_API_URL=http://localhost:8000
```

## Dependencies Installed
- react-router-dom: Routing
- axios: HTTP client
- recharts: Charts and data visualization
- @tanstack/react-table: Advanced table functionality
- All existing UI components (Radix UI)

## Implementation Priority

1. ✅ Backend API (Complete)
2. ✅ API service layer (Complete)
3. ✅ Authentication context (Complete)
4. 🚧 Login pages with backend integration
5. 🚧 Layout components (TopNav, Sidebar)
6. 🚧 Dashboard pages for each role
7. 🚧 Inventory management with Excel-style grid
8. 🚧 File upload integration
9. 🚧 Charts and analytics
10. 🚧 Document request workflow
11. 🚧 Activity logs display
12. 🚧 User and department management

## Database Integration

The application connects to the `cdmis_db` MySQL database through the PHP backend API. The database includes:
- users
- departments
- records
- record_files
- document_requests
- activity_logs
- disposition_schedule
- publicly_available_documents (view)

## Key Features

### Filtration Engine
The backend automatically filters documents based on the `restrictions` column:
- Documents marked as "Publicly Available" appear in the Document List pages
- Staff see only their department's public documents
- Custodians see all public documents from all departments

### Record Disposal Reminder
- Uses `calculated_disposal_date` from the database
- Triggered by `disposition_provision` matched against `disposition_schedule`
- Displayed on Admin and Custodian dashboards

### Activity Logging
- All user actions are automatically logged
- Database triggers handle record creation/update logging
- Manual logging for login, uploads, requests, etc.

## Next Steps for Full Implementation

Due to the complexity and size of this project (estimated 50+ component files), full implementation requires:

1. **Component Library**: Create all UI components needed
2. **Page Templates**: Build dashboard layouts for each role
3. **Data Management**: Implement state management for forms and tables
4. **File Handling**: Create upload/download UI
5. **Testing**: Test all user workflows
6. **Deployment**: Configure for production hosting

## Deployment Guide

### Local Development
1. Backend: `php -S localhost:8000` in backend directory
2. Frontend: `npm run dev` in CDMIS LOG IN PAGE directory

### Production Deployment
1. Set up MySQL database and import `cdmis_db.sql`
2. Configure backend `.env` with production database credentials
3. Build frontend: `npm run build`
4. Deploy backend to PHP hosting (Apache/Nginx)
5. Deploy frontend build to static hosting or same server
6. Configure HTTPS
7. Update CORS settings in backend

### Google Cloud Deployment
1. **Cloud SQL**: Create MySQL instance and import database
2. **App Engine or Compute Engine**: Deploy PHP backend
3. **Cloud Storage or App Engine**: Deploy React frontend
4. **Cloud CDN**: Optional for static assets
5. **Cloud Armor**: Optional for DDoS protection

## Security Considerations

1. ✅ Password hashing (PHP password_hash)
2. ✅ JWT authentication
3. ✅ Role-based access control
4. ✅ SQL injection prevention (prepared statements)
5. ✅ XSS protection headers
6. 🚧 HTTPS enforcement (configure in deployment)
7. 🚧 CSRF protection (add tokens)
8. 🚧 Rate limiting (add middleware)
9. 🚧 Input validation (add comprehensive validation)
