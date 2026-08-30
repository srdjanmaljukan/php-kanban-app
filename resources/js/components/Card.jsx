import React from 'react';
import { Draggable } from '@hello-pangea/dnd';

export default function Card({ card, index, onDelete }) {
    return (
        <Draggable draggableId={`card-${card.id}`} index={index}>
            {(provided, snapshot) => (
                <div
                    ref={provided.innerRef}
                    {...provided.draggableProps}
                    {...provided.dragHandleProps}
                    className={`kanban-card ${snapshot.isDragging ? 'dragging' : ''}`}
                >
                    <div className="card-header">
                        <h3>{card.title}</h3>
                        <button className="delete-card-btn" onClick={() => onDelete(card.id)}>×</button>
                    </div>
                    {card.description && <p className="card-description">{card.description}</p>}
                    {card.due_date && (
                        <span className="due-date">
                            {new Date(card.due_date).toLocaleDateString('en-GB')}
                        </span>
                    )}
                </div>
            )}
        </Draggable>
    );
}