# Ticketing System

Sistem manajemen tiket terintegrasi dengan frontend dan backend yang modern, aman, dan scalable.

## 📋 Daftar Isi

- [Tentang Aplikasi](#tentang-aplikasi)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Struktur Folder](#struktur-folder)
- [Instalasi](#instalasi)
- [Deployment](#deployment)
- [Dokumentasi](#dokumentasi)

## 🎯 Tentang Aplikasi

Ticketing System adalah aplikasi web untuk mengelola tiket dukungan pelanggan (support tickets). Aplikasi ini memungkinkan pengguna membuat, melacak, dan menyelesaikan tiket support dengan mudah melalui interface yang user-friendly.

### Use Cases

- **Pengguna**: Membuat tiket, melacak status, berkomunikasi dengan support team
- **Support Team**: Menerima tiket, memberikan respons, menyelesaikan masalah
- **Admin**: Mengelola users, tiket, laporan, dan konfigurasi sistem

## ✨ Fitur Utama

### Backend Features

- ✅ REST API yang aman dan scalable
- ✅ Autentikasi & autorisasi berbasis role
- ✅ Manajemen tiket (CRUD)
- ✅ User management
- ✅ Sistem notifikasi
- ✅ Database migrations
- ✅ API documentation

### Frontend Features

- ✅ Dashboard interaktif
- ✅ Form pembuatan tiket
- ✅ Tracking tiket real-time
- ✅ User profile management
- ✅ Responsive design
- ✅ Dark/Light theme support

### DevOps Features

- ✅ Docker containerization
- ✅ CI/CD pipeline otomatis
- ✅ Automated deployment
- ✅ Health monitoring
- ✅ Scalable architecture

## 🛠 Tech Stack

### Backend

- **Framework**: Laravel 11
- **Language**: PHP 8.1+
- **Database**: MySQL/PostgreSQL
- **API**: REST API
- **Authentication**: Laravel Passport/Sanctum
- **Testing**: PHPUnit

### Frontend

- **Framework**: React/Vue (based on implementation)
- **Styling**: Tailwind CSS / Bootstrap
- **State Management**: Redux/Vuex
- **HTTP Client**: Axios

### DevOps & Infrastructure

- **Containerization**: Docker & Docker Compose
- **CI/CD**: GitHub Actions
- **Server**: Ubuntu Linux
- **Web Server**: Nginx
- **Reverse Proxy**: Available for load balancing
- **Monitoring**: Docker health checks

## 📁 Struktur Folder

```
ticketing/
├── backend/               # Laravel REST API
│   ├── app/              # Application logic
│   ├── routes/           # API routes
│   ├── database/         # Migrations & seeds
│   ├── Dockerfile        # Docker configuration
│   └── .env.example      # Environment variables
├── frontend/             # React/Vue application
│   ├── src/              # Source code
│   ├── public/           # Static files
│   ├── Dockerfile        # Docker configuration
│   └── package.json      # Dependencies
├── .github/
│   └── workflows/
│       └── deploy.yml    # CI/CD pipeline
└── README.md             # This file
```

## 🚀 Instalasi

### Prerequisites

- Docker & Docker Compose
- Git
- Node.js 16+ (untuk development frontend)
- PHP 8.1+ (untuk development backend)

### Quick Start dengan Docker

1. **Clone Repository**

```bash
git clone <repository-url>
cd ticketing
```

2. **Setup Environment**

```bash
# Copy environment files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Generate Laravel key
docker-compose run backend php artisan key:generate
```

3. **Run dengan Docker Compose**

```bash
docker-compose up -d
```

4. **Setup Database**

```bash
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan seed:db
```

5. **Akses Aplikasi**

- Frontend: http://localhost:3000
- Backend API: http://localhost:5000
- Admin Panel: http://localhost:3000/admin

### Development Setup (Local)

**Backend**

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

**Frontend**

```bash
cd frontend
npm install
npm run dev
```

## 🔐 Environment Variables

### Backend (.env)

```env
APP_NAME=TicketingSystem
APP_ENV=production
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ticketing
DB_USERNAME=root
DB_PASSWORD=secret

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
```

### Frontend (.env)

```env
REACT_APP_API_URL=http://localhost:5000/api
REACT_APP_API_TIMEOUT=30000
```

## 📦 Deployment

### Automatic Deployment (CI/CD)

Setiap push ke branch `main` atau `master` akan trigger deployment otomatis:

1. **Build Stage**

   - Build Docker image backend
   - Build Docker image frontend
   - Push ke Docker Hub

2. **Deploy Stage**
   - SSH ke production server
   - Pull latest images
   - Stop & remove old containers
   - Run new containers
   - Cleanup images

**Production URLs**

- Frontend: http://your-domain.com
- Backend API: http://your-domain.com/api

### Manual Deployment

```bash
# Build images
docker build -t ticketing-backend ./backend
docker build -t ticketing-frontend ./frontend

# Push ke registry
docker push ticketing-backend
docker push ticketing-frontend

# SSH ke server dan pull/restart
ssh user@server
docker pull ticketing-backend
docker pull ticketing-frontend
docker-compose restart
```

## 📚 Dokumentasi

### API Documentation

- Backend API docs: `/api/documentation`
- Postman Collection: `docs/postman-collection.json`

### Database Schema

- Lihat `backend/database/migrations` untuk struktur database

### Setup Guide

- [Backend Setup](./backend/README.md)
- [Frontend Setup](./frontend/README.md)

## 🧪 Testing

**Backend Tests**

```bash
cd backend
php artisan test
```

**Frontend Tests**

```bash
cd frontend
npm test
```

## 🐛 Troubleshooting

### Port sudah terpakai

```bash
# Change port di docker-compose.yml
# atau gunakan port berbeda
docker-compose up -p 8000:5000
```

### Database connection error

```bash
# Check database container
docker-compose logs db

# Restart database
docker-compose restart db
```

### Permission denied

```bash
# Fix permissions
chmod -R 777 backend/storage backend/bootstrap
```

## 📞 Support & Kontribusi

Untuk pertanyaan atau saran:

- Buat GitHub Issue
- Pull Request dengan improvement
- Hubungi development team

## 📄 License

MIT License - Lihat [LICENSE](LICENSE) untuk detail

## 👥 Tim Development

- **Backend**: PHP/Laravel Developer
- **Frontend**: React/Vue Developer
- **DevOps**: Docker & CI/CD Engineer
- **PM**: Project Manager

---

**Last Updated**: January 7, 2026
