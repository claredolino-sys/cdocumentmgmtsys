// API Configuration
export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export const API_ENDPOINTS = {
  // Auth
  LOGIN: '/auth/login',
  PROFILE: '/auth/profile',
  CHANGE_PASSWORD: '/auth/change-password',
  
  // Records
  RECORDS: '/records',
  RECORD_BY_ID: (id: number) => `/records/${id}`,
  DISPOSAL_REMINDERS: '/records/disposal-reminders',
  PUBLIC_DOCUMENTS: '/records/public',
  
  // Departments
  DEPARTMENTS: '/departments',
  DEPARTMENT_BY_ID: (id: number) => `/departments/${id}`,
  DEPARTMENT_ANALYTICS: '/departments/analytics',
  
  // Users
  USERS: '/users',
  USER_BY_ID: (id: number) => `/users/${id}`,
  USER_RESET_PASSWORD: (id: number) => `/users/${id}/reset-password`,
  
  // Document Requests
  REQUESTS: '/requests',
  REQUEST_BY_ID: (id: number) => `/requests/${id}`,
  
  // Activity Logs
  ACTIVITY_LOGS: '/activity-logs',
  
  // Files
  FILES: '/files',
  FILES_BY_RECORD: (recordId: number) => `/files/${recordId}`,
  FILE_BY_ID: (fileId: number) => `/files/${fileId}`,
};
