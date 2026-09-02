# Kanban Board App

A full-stack project management application built with Laravel and React, featuring drag-and-drop task boards similar to Trello. Users can create boards, organize tasks into customizable columns, and collaborate with team members in real time.

## Features

- **Authentication** — Secure registration/login via Laravel Sanctum (token-based API auth)
- **Boards** — Create, rename, and delete project boards
- **Columns** — Add custom columns to organize workflow (defaults to To Do / In Progress / Done)
- **Cards** — Create tasks with title, description, and due date
- **Drag & Drop** — Reorder cards within a column or move them between columns
- **Collaboration** — Invite other registered users to a board by email; owners can manage membership
- **Role-based permissions** — Board owners have full control; members can manage cards but not board settings or membership
- **Automated tests** — PHPUnit feature tests covering authentication, authorization, and core board/card logic

## Tech Stack

**Backend**
- Laravel 11
- Laravel Sanctum (API token authentication)
- MySQL
- PHPUnit (feature testing)

**Frontend**
- React 19
- React Router (client-side routing)
- Axios (API communication)
- @hello-pangea/dnd (drag-and-drop)
- Vite (build tool, via `laravel-vite-plugin`)

## Architecture

This is a decoupled SPA: Laravel serves a pure REST API (`/api/*` routes) and Laravel Sanctum handles authentication via bearer tokens. React is the sole frontend, rendered client-side and talking to the backend exclusively through the API — there is no server-rendered Blade UI beyond the single shell page that bootstraps React.

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL

### Installation

1. Clone the repository and install dependencies:
```bash
   git clone <repo-url>
   cd kanban-app
   composer install
   npm install
```

2. Copy the environment file and generate an app key:
```bash
   cp .env.example .env
   php artisan key:generate
```

3. Create a MySQL database and update your `.env` with your database credentials:
```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kanban_db
   DB_USERNAME=root
   DB_PASSWORD=
```

4. Run migrations:
```bash
   php artisan migrate
```

5. Start the Laravel server and the Vite dev server (in two separate terminals):
```bash
   php artisan serve
   npm run dev
```

6. Open `http://127.0.0.1:8000` in your browser.

### Running Tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database and won't affect your local MySQL data.

## Deployment

The app is deployment-ready for platforms like Railway, Render, or DigitalOcean (standard Laravel + MySQL setup, no Docker required). For this project, deployment was intentionally left to local demonstration — no hosting provider currently offers a free tier suited to keeping a two-service app (web + database) running continuously at no cost.

## API Overview

All endpoints are prefixed with `/api` and, except for `register`/`login`, require a `Authorization: Bearer <token>` header.

| Method | Endpoint | Description |
|---|---|---|
| POST | `/register` | Create an account, returns a token |
| POST | `/login` | Authenticate, returns a token |
| POST | `/logout` | Revoke the current token |
| GET | `/user` | Get the authenticated user |
| GET | `/boards` | List boards the user belongs to |
| POST | `/boards` | Create a board (auto-creates 3 default columns) |
| GET | `/boards/{id}` | Get a board with columns, cards, and members |
| PUT | `/boards/{id}` | Rename a board (owner only) |
| DELETE | `/boards/{id}` | Delete a board (owner only) |
| POST | `/boards/{id}/columns` | Add a column |
| PUT | `/columns/{id}` | Rename or reorder a column |
| DELETE | `/columns/{id}` | Delete a column |
| POST | `/columns/{id}/cards` | Add a card |
| PUT | `/cards/{id}` | Update, move, or reorder a card |
| DELETE | `/cards/{id}` | Delete a card |
| POST | `/boards/{id}/members` | Invite a user by email (owner only) |
| DELETE | `/boards/{id}/members/{userId}` | Remove a member (owner only) |