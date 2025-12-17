# Ticketing System

A full-stack ticketing system built with Laravel (backend) and Vue.js (frontend), containerized with Docker.

## 🚀 Tech Stack

### Backend
- **Framework**: Laravel
- **Database**: MySQL 8.3
- **Server**: Apache/Nginx
- **Container**: Docker

### Frontend
- **Framework**: Vue.js
- **Build Tool**: Vite
- **Server**: Nginx
- **Container**: Docker

## 📁 Project Structure

```
ticketing/
├── backend/              # Laravel backend application
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── dockerfile
│   └── ...
├── frontend/             # Vue.js frontend application
│   ├── src/
│   ├── public/
│   ├── Dockerfile
│   └── ...
├── docker-compose.yml    # Unified Docker configuration
├── .gitignore
└── README.md
```

## 🛠️ Setup & Installation

### Prerequisites
- Docker & Docker Compose installed
- Git installed

### Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ticketing
   ```

2. **Configure environment variables**
   
   Backend:
   ```bash
   cd backend
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Start all services with Docker**
   ```bash
   docker-compose up -d
   ```

4. **Access the applications**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:5000
   - MySQL: localhost:3307

### Initial Setup (First Time Only)

After starting the containers, run these commands for the backend:

```bash
# Enter backend container
docker exec -it ticketing_backend bash

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

## 🐳 Docker Services

The `docker-compose.yml` includes three services:

1. **backend** - Laravel application (Port 5000)
2. **frontend** - Vue.js application (Port 3000)
3. **db** - MySQL database (Port 3307)

All services are connected via `ticketing-network` bridge network.

## 📝 Development

### Backend Development
```bash
cd backend
composer install
php artisan serve
```

### Frontend Development
```bash
cd frontend
npm install
npm run dev
```

## 🔧 Useful Commands

### Docker Commands
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose up -d --build

# Access backend container
docker exec -it ticketing_backend bash

# Access frontend container
docker exec -it ticketing_frontend bash

# Access database
docker exec -it ticketing_db mysql -u root -p
```

### Laravel Commands (inside backend container)
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name
```

## 📦 Production Deployment

For production deployment, make sure to:

1. Set `APP_ENV=production` in backend `.env`
2. Set `APP_DEBUG=false` in backend `.env`
3. Configure proper database credentials
4. Use proper volume mounts or build images with code included
5. Set up SSL/TLS certificates
6. Configure reverse proxy (nginx/traefik)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👥 Authors

- Your Name - Initial work

## 🙏 Acknowledgments

- Laravel Framework
- Vue.js Framework
- Docker
