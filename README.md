# Task Management System

Interactive task manager built with **Laravel 12**, **Livewire 3.6**, **FluxUI 2.2 Free**, **TailwindCSS 4**, and **PHP 8.4**.  
Features: create, edit, delete tasks, drag-and-drop reordering with priorities, and project organization.

![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)
![Livewire](https://img.shields.io/badge/Livewire-3.6-green.svg)
![FluxUI](https://img.shields.io/badge/FluxUI-2.2-purple.svg)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.0-38b2ac.svg)

---

## ✨ Features
- Create, edit, delete tasks
- Drag & drop reordering (priorities auto-update per project)
- Create and assign tasks to projects
- Filter tasks by project or view all
- Real-time UI updates with Livewire
- Responsive design with TailwindCSS

---

## 🛠️ Tech Stack
- **Backend:** Laravel 12 (PHP 8.4)
- **Frontend:** Livewire 3.6, FluxUI 2.2 Free, TailwindCSS 4
- **Database:** MySQL (default Laravel Sail service)
- **Drag & Drop:** SortableJS
- **Build Tool:** Vite

---

## 🚀 Quick Setup (Docker Compose)

### 1. Start containers
```bash
docker compose up -d --build
```

### 2. Install dependencies
```bash
docker compose exec -it laravel.test bash -c "composer install"
docker compose exec -it laravel.test bash -c "npm install"
```

### 3. Environment & key
```bash
cp .env.example .env
docker compose exec -it laravel.test bash -c "php artisan key:generate --force"
```

### 4. Database
```bash
docker compose exec -it laravel.test bash -c "php artisan migrate"
```

### 5. Build frontend
```bash
docker compose exec -it laravel.test bash -c "npm run build"
```
or for development with hot reload:
```bash
docker compose exec -it laravel.test bash -c "npm run dev"
```

 Now open http://localhost 🎉

© 2025 Developed by: **Luis Manuel Zuñiga Moreno**  

[![Luis Manuel Zuñiga Moreno](https://img.shields.io/badge/LinkedIn-Profile-blue)](https://www.linkedin.com/in/devluism/)
