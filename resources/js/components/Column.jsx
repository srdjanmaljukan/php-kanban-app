import React, { useState } from 'react';
import { Droppable } from '@hello-pangea/dnd';
import Card from './Card';

export default function Column({ column, onAddCard, onDeleteCard, onDeleteColumn, isSyncing }) {
    const [showAddCard, setShowAddCard] = useState(false);
    const [newCardTitle, setNewCardTitle] = useState('');

    function handleAddCard(event) {
        event.preventDefault();
        if (newCardTitle.trim() === '') return;

        onAddCard(column.id, newCardTitle);
        setNewCardTitle('');
        setShowAddCard(false);
    }

    return (
        <div className="kanban-column">
            <div className="column-header">
                <h2>{column.name}</h2>
                <button className="delete-column-btn" onClick={() => onDeleteColumn(column.id)}>×</button>
            </div>

            <Droppable droppableId={`column-${column.id}`} isDropDisabled={isSyncing}>
                {(provided, snapshot) => (
                    <div
                        ref={provided.innerRef}
                        {...provided.droppableProps}
                        className={`card-list ${snapshot.isDraggingOver ? 'dragging-over' : ''}`}
                    >
                        {column.cards.map((card, index) => (
                            <Card key={card.id} card={card} index={index} onDelete={onDeleteCard} />
                        ))}
                        {provided.placeholder}
                    </div>
                )}
            </Droppable>

            {showAddCard ? (
                <form onSubmit={handleAddCard} className="add-card-form">
                    <input
                        type="text"
                        placeholder="Card title"
                        value={newCardTitle}
                        onChange={(e) => setNewCardTitle(e.target.value)}
                        autoFocus
                    />
                    <div className="add-card-actions">
                        <button type="submit">Add</button>
                        <button type="button" onClick={() => setShowAddCard(false)}>Cancel</button>
                    </div>
                </form>
            ) : (
                <button className="show-add-card-btn" onClick={() => setShowAddCard(true)}>+ Add card</button>
            )}
        </div>
    );
}