import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    // Pri prvom učitavanju aplikacije, provjeravamo da li već postoji
    // sačuvan token, i ako da, povlačimo podatke o korisniku
    useEffect(() => {
        const token = localStorage.getItem('token');

        if (token) {
            api.get('/user')
                .then((response) => setUser(response.data))
                .catch(() => localStorage.removeItem('token'))
                .finally(() => setLoading(false));
        } else {
            setLoading(false);
        }
    }, []);

    async function login(email, password) {
        const response = await api.post('/login', { email, password });
        localStorage.setItem('token', response.data.token);
        setUser(response.data.user);
    }

    async function register(name, email, password) {
        const response = await api.post('/register', { name, email, password });
        localStorage.setItem('token', response.data.token);
        setUser(response.data.user);
    }

    async function logout() {
        await api.post('/logout');
        localStorage.removeItem('token');
        setUser(null);
    }

    return (
        <AuthContext.Provider value={{ user, loading, login, register, logout }}>
            {children}
        </AuthContext.Provider>
    );
}

// Custom hook — pogodniji način da komponente pristupe Context-u
export function useAuth() {
    return useContext(AuthContext);
}