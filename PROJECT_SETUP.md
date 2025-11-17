# Mini School Attendance System - Installation Documentation

## Technical Stack

**Backend:** Laravel 10 + PHP 8.1+

**Frontend:** Vue.js 3 + Vuetify 3 + Node.js 22+

**Database:** MySQL 8.0 (root user)

**Authentication:** Laravel Sanctum

**State Management:** Pinia

**Charts:** Chart.js

**Containerization:** Docker + Docker Compose

---

## 1. Project Directory Structure

```
project-root/
├── backend/        # Laravel 10 project
├── frontend/       # Vue 3 + Vuetify project
├── docker-compose.yml
├── .env            # Docker environment variables
```

---

## 2. Backend Structure (Laravel 10)

```
backend/
├── app/
│   ├── Console/Commands/GenerateAttendanceReport.php
│   ├── Events/AttendanceRecorded.php
│   ├── Exceptions/Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── AdminController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── StudentController.php
│   │   │   └── AttendanceController.php
│   │   ├── Middleware/AdminMiddleware.php
│   │   ├── Middleware/TeacherMiddleware.php
│   │   ├── Requests/LoginRequest.php
│   │   ├── Requests/RegisterRequest.php
│   │   ├── Requests/StudentRequest.php
│   │   ├── Requests/AttendanceRequest.php
│   │   ├── Resources/StudentResource.php
│   │   ├── Resources/AttendanceResource.php
│   │   └── Resources/UserResource.php
│   ├── Listeners/SendAttendanceNotification.php
│   ├── Models/User.php
│   ├── Models/Student.php
│   ├── Models/Attendance.php
│   ├── Models/AttendanceSummary.php
│   ├── Providers/AppServiceProvider.php
│   ├── Providers/AuthServiceProvider.php
│   ├── Providers/EventServiceProvider.php
│   └── Services/
│       ├── DashboardService.php
│       ├── AttendanceService.php
│       └── StudentService.php
├── bootstrap/app.php
├── config/
├── database/
├── public/
├── routes/
├── storage/
├── tests/
├── .env.example
├── composer.json
└── artisan
```

---

## 3. Frontend Structure (Vue.js + Vuetify)

```
frontend/
├── public/
├── src/
│   ├── assets/
│   ├── components/
│   ├── composables/
│   ├── layouts/
│   ├── router/
│   ├── services/
│   ├── stores/
│   ├── utils/
│   ├── views/
│   ├── App.vue
│   └── main.js
├── package.json
└── vite.config.js
```

---

## 4. Docker Setup

### 4.1 `docker-compose.yml`

```yaml
version: '3.9'

services:
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: laravel_backend
    ports:
      - "8000:8000"
    volumes:
      - ./backend:/var/www/html
    environment:
      DB_HOST: db
      DB_PORT: 3306
      DB_DATABASE: mini_school
      DB_USERNAME: root
      DB_PASSWORD: root123
    depends_on:
      - db
    networks:
      - mini_school_net

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: vue_frontend
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/var/www/html
    networks:
      - mini_school_net

  db:
    image: mysql:8.0
    container_name: mysql_db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: root123
      MYSQL_DATABASE: mini_school
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - mini_school_net

volumes:
  db_data:

networks:
  mini_school_net:
    driver: bridge
```

---

### 4.2 Laravel Backend `Dockerfile` (`backend/Dockerfile`)

```dockerfile
FROM php:8.1-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    libonig-dev \
    mariadb-client \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
```

### 4.3 Vue Frontend `Dockerfile` (`frontend/Dockerfile`)

```dockerfile
FROM node:22

WORKDIR /var/www/html

COPY . /var/www/html

RUN npm install

EXPOSE 3000

CMD ["npm", "run", "dev"]
```

### 4.4 Laravel `.env` Configuration

```dotenv
APP_NAME=MiniSchool
APP_ENV=local
APP_KEY=base64:GENERATE_YOUR_KEY
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=mini_school
DB_USERNAME=root
DB_PASSWORD=root123

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 5. Installation Steps

### 5.1 Start Docker Containers

```bash
docker-compose up -d --build
```

### 5.2 Laravel Setup (Backend)

```bash
docker exec -it laravel_backend bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
exit
```

### 5.3 Vue Frontend

* Accessible at [http://localhost:3000](http://localhost:3000)

# Project Setup Documentation

## 5.3 Vue Frontend

- Accessible at: **http://localhost:3000**

### Frontend `.env` Setup

Create a `.env` file inside the **frontend** directory and add the following:

```env
VITE_API_URL=http://localhost:8000/api
```


### 5.4 Backend API

* Accessible at [http://localhost:8000](http://localhost:8000)

### 5.5 Database

* MySQL root user: `root` / `root123`
* Database: `mini_school`
* Host: `localhost:3306`

---

## 6. Laravel Artisan Commands

* Run migrations: `php artisan migrate`
* Seed database: `php artisan db:seed`
* Generate attendance report:

```bash
php artisan attendance:generate-report 2024-01
php artisan attendance:generate-report 2024-01 --class=1
php artisan attendance:generate-report 2024-01 --save-file
```

* **Generate Custom Artisan Command**:

```bash
php artisan make:command GenerateAttendanceReport
```

---

## 7. API Testing (curl)

```bash
curl -X POST http://localhost:8000/api/login
curl -X GET http://localhost:8000/api/students
curl -X POST http://localhost:8000/api/attendance/bulk
```

---

## 8. Notes

* **Root User Only:** MySQL always uses root (no additional users).
* **Persistent Data:** Docker volume `db_data` preserves database.
* **Frontend Dev:** Hot reload works through port `3000`.
* **Backend Dev:** Laravel server runs on port `8000`.

---


