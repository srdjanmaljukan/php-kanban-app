import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import BoardsListPage from './pages/BoardsListPage';
import BoardDetailPage from './pages/BoardDetailPage';

// Wrapper koji dozvoljava pristup samo ulogovanim korisnicima
function ProtectedRoute({ children }) {
    const { user, loading } = useAuth();

    if (loading) {
        return <p>Loading...</p>;
    }

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    return children;
}

function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    <Route path="/login" element={<LoginPage />} />
                    <Route path="/register" element={<RegisterPage />} />
                    <Route
                        path="/boards"
                        element={
                            <ProtectedRoute>
                                <BoardsListPage />
                            </ProtectedRoute>
                        }
                    />
                    <Route path="/" element={<Navigate to="/boards" replace />} />
                    <Route
                        path="/boards/:id"
                        element={
                            <ProtectedRoute>
                                <BoardDetailPage />
                            </ProtectedRoute>
                        }
                    />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}

const container = document.getElementById('app');
const root = createRoot(container);
root.render(<App />);