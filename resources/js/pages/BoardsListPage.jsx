import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../api';
import { useAuth } from '../context/AuthContext';

export default function BoardsListPage() {
    const [boards, setBoards] = useState([]);
    const [loading, setLoading] = useState(true);
    const [newBoardName, setNewBoardName] = useState('');
    const [error, setError] = useState('');
    const { user, logout } = useAuth();

    useEffect(() => {
        fetchBoards();
    }, []);

    async function fetchBoards() {
        try {
            const response = await api.get('/boards');
            setBoards(response.data);
        } catch (err) {
            setError('Failed to load boards.');
        } finally {
            setLoading(false);
        }
    }

    async function handleCreateBoard(event) {
        event.preventDefault();
        setError('');

        if (newBoardName.trim() === '') {
            return;
        }

        try {
            const response = await api.post('/boards', { name: newBoardName });
            setBoards([response.data, ...boards]);
            setNewBoardName('');
        } catch (err) {
            setError('Failed to create board.');
        }
    }

    if (loading) {
        return <p>Loading boards...</p>;
    }

    return (
        <div className="boards-page">
            <nav>
                <h1 className="logo">My Boards</h1>
                <div className="nav-links">
                    <span>Hi, {user?.name}</span>
                    <button onClick={logout}>Logout</button>
                </div>
            </nav>

            <main>
                <form onSubmit={handleCreateBoard} className="new-board-form">
                    <input
                        type="text"
                        placeholder="New board name"
                        value={newBoardName}
                        onChange={(e) => setNewBoardName(e.target.value)}
                    />
                    <button type="submit">Create Board</button>
                </form>

                {error && <p className="error">{error}</p>}

                <div className="board-grid">
                    {boards.length === 0 ? (
                        <p>You have no boards yet. Create one above!</p>
                    ) : (
                        boards.map((board) => (
                            <Link to={`/boards/${board.id}`} key={board.id} className="board-card">
                                <h2>{board.name}</h2>
                                <span className="role-badge">{board.pivot?.role}</span>
                            </Link>
                        ))
                    )}
                </div>
            </main>
        </div>
    );
}