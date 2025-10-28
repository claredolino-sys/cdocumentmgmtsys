import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';

// Import login components
import LoginSelection from './pages/common/LoginSelection';
import AdminLogin from './pages/common/AdminLogin';
import CustodianLogin from './pages/common/CustodianLogin';
import StaffLogin from './pages/common/StaffLogin';

// Import dashboard layouts
import AdminLayout from './pages/admin/AdminLayout';
import CustodianLayout from './pages/custodian/CustodianLayout';
import StaffLayout from './pages/staff/StaffLayout';

// Protected Route Component
interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles: string[];
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children, allowedRoles }) => {
  const { isAuthenticated, user, isLoading } = useAuth();

  if (isLoading) {
    return <div className="flex items-center justify-center h-screen">Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/" replace />;
  }

  if (user && !allowedRoles.includes(user.role)) {
    return <Navigate to="/" replace />;
  }

  return <>{children}</>;
};

function AppRoutes() {
  return (
    <Routes>
      {/* Public routes */}
      <Route path="/" element={<LoginSelection />} />
      <Route path="/login/admin" element={<AdminLogin />} />
      <Route path="/login/custodian" element={<CustodianLogin />} />
      <Route path="/login/staff" element={<StaffLogin />} />

      {/* Admin routes */}
      <Route
        path="/admin/*"
        element={
          <ProtectedRoute allowedRoles={['Admin']}>
            <AdminLayout />
          </ProtectedRoute>
        }
      />

      {/* Departmental Record Custodian routes */}
      <Route
        path="/custodian/*"
        element={
          <ProtectedRoute allowedRoles={['Departmental Record Custodian']}>
            <CustodianLayout />
          </ProtectedRoute>
        }
      />

      {/* Staff routes */}
      <Route
        path="/staff/*"
        element={
          <ProtectedRoute allowedRoles={['Staff']}>
            <StaffLayout />
          </ProtectedRoute>
        }
      />

      {/* Catch all */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

export default function App() {
  return (
    <Router>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </Router>
  );
}
