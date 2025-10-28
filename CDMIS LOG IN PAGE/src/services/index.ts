import apiClient from './api';
import { API_ENDPOINTS } from '../config/api';
import {
  LoginCredentials,
  LoginResponse,
  User,
  Record,
  Department,
  DepartmentAnalytics,
  DocumentRequest,
  ActivityLog,
  RecordFile,
  PublicDocument,
} from '../types';

// Auth Services
export const authService = {
  login: async (credentials: LoginCredentials): Promise<LoginResponse> => {
    const response = await apiClient.post<LoginResponse>(API_ENDPOINTS.LOGIN, credentials);
    return response.data;
  },

  getProfile: async (): Promise<User> => {
    const response = await apiClient.get<User>(API_ENDPOINTS.PROFILE);
    return response.data;
  },

  changePassword: async (oldPassword: string, newPassword: string): Promise<{ message: string }> => {
    const response = await apiClient.post(API_ENDPOINTS.CHANGE_PASSWORD, {
      old_password: oldPassword,
      new_password: newPassword,
    });
    return response.data;
  },
};

// Records Services
export const recordsService = {
  getRecords: async (): Promise<Record[]> => {
    const response = await apiClient.get<Record[]>(API_ENDPOINTS.RECORDS);
    return response.data;
  },

  getRecord: async (id: number): Promise<Record> => {
    const response = await apiClient.get<Record>(API_ENDPOINTS.RECORD_BY_ID(id));
    return response.data;
  },

  createRecord: async (record: Partial<Record>): Promise<{ message: string; id: number }> => {
    const response = await apiClient.post(API_ENDPOINTS.RECORDS, record);
    return response.data;
  },

  updateRecord: async (id: number, record: Partial<Record>): Promise<{ message: string }> => {
    const response = await apiClient.put(API_ENDPOINTS.RECORD_BY_ID(id), record);
    return response.data;
  },

  deleteRecord: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(API_ENDPOINTS.RECORD_BY_ID(id));
    return response.data;
  },

  getDisposalReminders: async (): Promise<Record[]> => {
    const response = await apiClient.get<Record[]>(API_ENDPOINTS.DISPOSAL_REMINDERS);
    return response.data;
  },

  getPublicDocuments: async (): Promise<PublicDocument[]> => {
    const response = await apiClient.get<PublicDocument[]>(API_ENDPOINTS.PUBLIC_DOCUMENTS);
    return response.data;
  },
};

// Departments Services
export const departmentsService = {
  getDepartments: async (): Promise<Department[]> => {
    const response = await apiClient.get<Department[]>(API_ENDPOINTS.DEPARTMENTS);
    return response.data;
  },

  createDepartment: async (name: string): Promise<{ message: string; id: number }> => {
    const response = await apiClient.post(API_ENDPOINTS.DEPARTMENTS, { name });
    return response.data;
  },

  updateDepartment: async (id: number, name: string): Promise<{ message: string }> => {
    const response = await apiClient.put(API_ENDPOINTS.DEPARTMENT_BY_ID(id), { name });
    return response.data;
  },

  deleteDepartment: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(API_ENDPOINTS.DEPARTMENT_BY_ID(id));
    return response.data;
  },

  getDepartmentAnalytics: async (): Promise<DepartmentAnalytics[]> => {
    const response = await apiClient.get<DepartmentAnalytics[]>(API_ENDPOINTS.DEPARTMENT_ANALYTICS);
    return response.data;
  },
};

// Users Services
export const usersService = {
  getUsers: async (): Promise<User[]> => {
    const response = await apiClient.get<User[]>(API_ENDPOINTS.USERS);
    return response.data;
  },

  createUser: async (user: Partial<User> & { password: string }): Promise<{ message: string; id: number }> => {
    const response = await apiClient.post(API_ENDPOINTS.USERS, user);
    return response.data;
  },

  updateUser: async (id: number, user: Partial<User>): Promise<{ message: string }> => {
    const response = await apiClient.put(API_ENDPOINTS.USER_BY_ID(id), user);
    return response.data;
  },

  deleteUser: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(API_ENDPOINTS.USER_BY_ID(id));
    return response.data;
  },

  resetPassword: async (id: number, newPassword: string): Promise<{ message: string }> => {
    const response = await apiClient.post(API_ENDPOINTS.USER_RESET_PASSWORD(id), { new_password: newPassword });
    return response.data;
  },
};

// Document Requests Services
export const requestsService = {
  getRequests: async (): Promise<DocumentRequest[]> => {
    const response = await apiClient.get<DocumentRequest[]>(API_ENDPOINTS.REQUESTS);
    return response.data;
  },

  createRequest: async (request: Partial<DocumentRequest>): Promise<{ message: string; id: number }> => {
    const response = await apiClient.post(API_ENDPOINTS.REQUESTS, request);
    return response.data;
  },

  updateRequestStatus: async (id: number, status: string): Promise<{ message: string }> => {
    const response = await apiClient.put(API_ENDPOINTS.REQUEST_BY_ID(id), { status });
    return response.data;
  },
};

// Activity Logs Services
export const activityLogsService = {
  getLogs: async (): Promise<ActivityLog[]> => {
    const response = await apiClient.get<ActivityLog[]>(API_ENDPOINTS.ACTIVITY_LOGS);
    return response.data;
  },
};

// Files Services
export const filesService = {
  uploadFile: async (recordId: number, file: File): Promise<{ message: string; file_id: number; filename: string }> => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('record_id', recordId.toString());

    const response = await apiClient.post(API_ENDPOINTS.FILES, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  getFiles: async (recordId: number): Promise<RecordFile[]> => {
    const response = await apiClient.get<RecordFile[]>(API_ENDPOINTS.FILES_BY_RECORD(recordId));
    return response.data;
  },

  deleteFile: async (fileId: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(API_ENDPOINTS.FILE_BY_ID(fileId));
    return response.data;
  },
};
