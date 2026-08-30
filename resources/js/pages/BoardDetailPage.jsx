import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { DragDropContext } from '@hello-pangea/dnd';
import api from '../api';
import Column from '../components/Column';

export default function BoardDetailPage() {
    const { id } = useParams();
    const [board, setBoard] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [newColumnName, setNewColumnName] = useState('');
    const [inviteEmail, setInviteEmail] = useState('');
    const [inviteMessage, setInviteMessage] = useState('');
    const [isSyncing, setIsSyncing] = useState(false);

    useEffect(() => {
        fetchBoard();
    }, [id]);

    async function fetchBoard() {
        try {
            const response = await api.get(`/boards/${id}`);
            setBoard(response.data);
        } catch (err) {
            setError('Failed to load board.');
        } finally {
            setLoading(false);
        }
    }

    async function handleAddColumn(event) {
        event.preventDefault();
        if (newColumnName.trim() === '') return;

        const response = await api.post(`/boards/${id}/columns`, { name: newColumnName });
        setBoard({
            ...board,
            columns: [...board.columns, { ...response.data, cards: [] }],
        });
        setNewColumnName('');
    }

    async function handleDeleteColumn(columnId) {
        if (!confirm('Delete this column and all its cards?')) return;

        await api.delete(`/columns/${columnId}`);
        setBoard({
            ...board,
            columns: board.columns.filter((c) => c.id !== columnId),
        });
    }

    async function handleAddCard(columnId, title) {
        const response = await api.post(`/columns/${columnId}/cards`, { title });
        setBoard({
            ...board,
            columns: board.columns.map((c) =>
                c.id === columnId ? { ...c, cards: [...c.cards, response.data] } : c
            ),
        });
    }

    async function handleDeleteCard(cardId) {
        await api.delete(`/cards/${cardId}`);
        setBoard({
            ...board,
            columns: board.columns.map((c) => ({
                ...c,
                cards: c.cards.filter((card) => card.id !== cardId),
            })),
        });
    }

    async function handleInviteMember(event) {
        event.preventDefault();
        setInviteMessage('');

        try {
            const response = await api.post(`/boards/${id}/members`, { email: inviteEmail });
            setInviteMessage(`${response.data.member.name} added successfully.`);
            setInviteEmail('');
            fetchBoard(); // osvježi listu članova
        } catch (err) {
            setInviteMessage(err.response?.data?.message || 'Failed to add member.');
        }
    }

    // Poziva se kad korisnik pusti karticu nakon prevlačenja
    async function handleDragEnd(result) {
        const { source, destination, draggableId } = result;

        if (!destination) return;
        if (source.droppableId === destination.droppableId && source.index === destination.index) {
            return;
        }

        const cardId = parseInt(draggableId.replace('card-', ''), 10);
        const sourceColumnId = parseInt(source.droppableId.replace('column-', ''), 10);
        const destColumnId = parseInt(destination.droppableId.replace('column-', ''), 10);

        const newColumns = board.columns.map((col) => ({ ...col, cards: [...col.cards] }));
        const sourceColumn = newColumns.find((c) => c.id === sourceColumnId);
        const destColumn = newColumns.find((c) => c.id === destColumnId);

        const [movedCard] = sourceColumn.cards.splice(source.index, 1);
        destColumn.cards.splice(destination.index, 0, movedCard);

        setBoard({ ...board, columns: newColumns });
        setIsSyncing(true);

        try {
            await api.put(`/cards/${cardId}`, {
                column_id: destColumnId,
                position: destination.index,
            });
        } catch (err) {
            fetchBoard();
        } finally {
            setIsSyncing(false);
        }
    }

    if (loading) return <p>Loading board...</p>;
    if (error) return <p className="error">{error}</p>;

    return (
        <div className="board-detail-page">
            <nav>
                <Link to="/boards" className="logo">← My Boards</Link>
                <h1>{board.name}</h1>
            </nav>

            <div className="board-content">
                <DragDropContext onDragEnd={handleDragEnd}>
                    <div className="columns-container">
                        {board.columns.map((column) => (
                            <Column
                                key={column.id}
                                column={column}
                                onAddCard={handleAddCard}
                                onDeleteCard={handleDeleteCard}
                                onDeleteColumn={handleDeleteColumn}
                                isSyncing={isSyncing}
                            />
                        ))}

                        <form onSubmit={handleAddColumn} className="add-column-form">
                            <input
                                type="text"
                                placeholder="New column name"
                                value={newColumnName}
                                onChange={(e) => setNewColumnName(e.target.value)}
                            />
                            <button type="submit">+ Add Column</button>
                        </form>
                    </div>
                </DragDropContext>

                <aside className="members-panel">
                    <h3>Members</h3>
                    <ul>
                        {board.members.map((member) => (
                            <li key={member.id}>
                                {member.name} <span className="role-badge">{member.pivot.role}</span>
                            </li>
                        ))}
                    </ul>

                    <form onSubmit={handleInviteMember} className="invite-form">
                        <input
                            type="email"
                            placeholder="Invite by email"
                            value={inviteEmail}
                            onChange={(e) => setInviteEmail(e.target.value)}
                            required
                        />
                        <button type="submit">Invite</button>
                    </form>

                    {inviteMessage && <p className="invite-message">{inviteMessage}</p>}
                </aside>
            </div>
        </div>
    );
}