import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const { login } = useAuth();
    const navigate = useNavigate();

    async function handleSubmit(event) {
        event.preventDefault();
        setError('');

        try {
            await login(email, password);
            navigate('/boards');
        } catch (err) {
            setError(err.response?.data?.message || 'Invalid credentials.');
        }
    }

    return (
        <div className="auth-page">
            <h1>Login</h1>

            {error && <p className="error">{error}</p>}

            <form onSubmit={handleSubmit}>
                <input
                    type="email"
                    placeholder="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />
                <input
                    type="password"
                    placeholder="Password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                />
                <button type="submit">Login</button>
            </form>

            <p>Don't have an account? <Link to="/register">Register</Link></p>
        </div>
    );
}