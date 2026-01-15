# News Management System - Symfony 7 with Docker

A Symfony 7 application running in Docker containers with PHP 8.3, Nginx, and MySQL 8.0.

## Prerequisites

- Docker
- Docker Compose

## Getting Started

### 1. Build and Start Containers

```bash
docker-compose up -d --build
```

### 2. Install Dependencies (if needed)

```bash
docker-compose exec app composer install
```

### 3. Access the Application

- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MySQL**: localhost:3307

## Services

- **app**: PHP 8.3-FPM container running Symfony
- **nginx**: Nginx web server
- **db**: MySQL 8.0 database
- **phpmyadmin**: phpMyAdmin for database management

## Database Configuration

- **Database Name**: news_management_system
- **Username**: symfony
- **Password**: symfony
- **Root Password**: root
- **Port**: 3307 (mapped from container's 3306)

## Useful Commands

### Start containers
```bash
docker-compose up -d
```

### Stop containers
```bash
docker-compose down
```

### View logs
```bash
docker-compose logs -f
```

### Execute Symfony commands
```bash
docker-compose exec app php bin/console [command]
```

### Access PHP container shell
```bash
docker-compose exec app bash
```

### Clear cache
```bash
docker-compose exec app php bin/console cache:clear
```

### Run migrations (when configured)
```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

## Project Structure

```
.
├── docker/
│   ├── nginx/
│   │   └── nginx.conf
│   └── php/
│       └── local.ini
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
└── [Symfony project files]
```

## Development

The application code is mounted as a volume, so changes to PHP files will be reflected immediately without rebuilding the container.

## Production Notes

For production deployment:
1. Change APP_ENV to `prod` in `.env`
2. Set a secure APP_SECRET
3. Update database credentials
4. Build optimized autoloader: `composer dump-autoload --optimize --no-dev --classmap-authoritative`
5. Use production-ready web server configuration
