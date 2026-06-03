# Car Wash Management App

PHP + MySQL application — dockerized XAMPP-equivalent stack with CI/CD via GitHub Actions.

---

## Stack

| Layer      | Technology           |
|------------|----------------------|
| Backend    | PHP 8.2              |
| Web server | Apache 2.4           |
| Database   | MySQL 8.0            |
| DB Admin   | phpMyAdmin 5.2       |
| Container  | Docker + Compose     |
| CI/CD      | GitHub Actions       |
| Registry   | Docker Hub           |

---

## Local Development

### Prerequisites
- Docker Desktop installed and running

### Start the full stack
```bash
docker compose up --build
```

| Service    | URL                        |
|------------|----------------------------|
| App        | http://localhost:8080       |
| phpMyAdmin | http://localhost:8081       |
| MySQL      | localhost:3306              |

### Stop
```bash
docker compose down
```

### Stop and wipe database volume
```bash
docker compose down -v
```

---

## Running Tests

### Install dependencies locally (optional — CI runs them automatically)
```bash
composer install
```

### Run all tests
```bash
vendor/bin/phpunit
```

### Run only unit tests
```bash
vendor/bin/phpunit --testsuite Unit
```

### Run only feature (DB) tests
```bash
vendor/bin/phpunit --testsuite Feature
```

---

## CI/CD Pipeline

```
Push to any branch
       │
       ▼
  PHPUnit Tests (Unit + Feature)
       │
       ├── FAIL → pipeline stops, no image built
       │
       └── PASS (main branch only)
              │
              ▼
       Docker image built
              │
              ▼
       Pushed to Docker Hub
       yourname/carwash-app:latest
       yourname/carwash-app:sha-xxxxxxx
```

### GitHub Secrets required

Go to **GitHub repo → Settings → Secrets → Actions** and add:

| Secret name          | Value                        |
|----------------------|------------------------------|
| `DOCKERHUB_USERNAME` | Your Docker Hub username     |
| `DOCKERHUB_TOKEN`    | Docker Hub access token      |

To create a Docker Hub token: https://hub.docker.com/settings/security

---

## Project Structure

```
├── application/
│   ├── php/          ← backend logic
│   ├── static/       ← CSS, JS, images
│   └── views/        ← HTML templates
├── tests/
│   ├── Unit/         ← pure logic tests (no DB)
│   └── Feature/      ← DB integration tests
├── docker/
│   └── apache.conf   ← Apache virtual host config
├── .github/
│   └── workflows/
│       └── ci-cd.yml ← GitHub Actions pipeline
├── Dockerfile
├── docker-compose.yml
├── docker-compose.test.yml
├── composer.json
├── phpunit.xml
├── car_wash_db.sql
└── index.html
```
