// User Types
export type UserRole = 'Admin' | 'Departmental Record Custodian' | 'Staff';

export interface User {
  id: number;
  school_id: string;
  full_name: string;
  email: string;
  role: UserRole;
  department_id: number | null;
  department_name?: string;
  profile_picture_url?: string;
}

// Auth Types
export interface LoginCredentials {
  school_id: string;
  password: string;
}

export interface LoginResponse {
  message: string;
  token: string;
  user: User;
}

// Record Types
export interface Record {
  id: number;
  record_series_title_description: string;
  period_covered?: string;
  volume?: string;
  record_medium?: string;
  restrictions?: string;
  location?: string;
  frequency_of_use?: string;
  duplication?: string;
  time_value?: 'T' | 'P';
  utility_value?: string;
  retention_period_active?: string;
  retention_period_storage?: string;
  retention_period_total?: string;
  disposition_provision?: string;
  date_of_record?: string;
  calculated_disposal_date?: string;
  department_id: number;
  department_name?: string;
  created_by_user_id: number;
  created_by_name?: string;
  created_at: string;
  updated_at: string;
}

// Department Types
export interface Department {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

// Document Request Types
export type RequestStatus = 'Pending' | 'Approved' | 'Denied';

export interface DocumentRequest {
  id: number;
  record_id: number;
  requester_user_id: number;
  purpose: string;
  id_document_path?: string;
  status: RequestStatus;
  request_date: string;
  approval_date?: string;
  approver_user_id?: number;
  record_series_title_description?: string;
  requester_name?: string;
  requester_email?: string;
  requester_department?: string;
  approver_name?: string;
}

// Activity Log Types
export interface ActivityLog {
  id: number;
  user_id: number;
  office: string;
  operation: string;
  action_date_time: string;
  record_series_title_description?: string;
  details?: string;
  user_name?: string;
  school_id?: string;
}

// File Types
export interface RecordFile {
  id: number;
  record_id: number;
  file_name: string;
  file_path: string;
  file_size: number;
  upload_date: string;
  uploaded_by_user_id: number;
  uploaded_by_name?: string;
}

// Analytics Types
export interface DepartmentAnalytics {
  department: string;
  document_count: number;
}

// Public Document Types
export interface PublicDocument {
  id: number;
  record_series_title_description: string;
  period_covered?: string;
  department_name: string;
  department_id: number;
}
