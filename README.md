# TaskCraft API

A robust Task & Project Management RESTful API built with **Laravel 11**, focusing on code quality, automated testing, asynchronous job queues, JWT authentication, a Service Layer architecture, OpenAPI/Swagger documentation, and multi-format report exports (PDF & Excel).

---

## Key Features

- **JWT Authentication**: Secure user registration, login, token refresh, and logout using JSON Web Tokens (`tymon/jwt-auth`).
- **Project Management & Access Control**: Full CRUD operations for managing projects with thin controllers, a dedicated `ProjectService`, and custom Form Requests.
- **Project Members & Granular Roles**: Multi-user collaboration using the `project_user` pivot table, supporting role-based permissions (`owner`, `member`, `viewer`).
- **Task Management & Advanced Filtering**: Create, assign, and track tasks with Eloquent scopes for filtering by status (`?status=`), priority (`?priority=`), and text search (`?search=`).
- **Business Rules & Restrictions**: Enforced rules including preventing new comments on `completed` tasks and restricting project modification to project owners.
- **File Uploads & Task Comments**: Task comments with file attachment uploads and dynamic `attachment_url` accessors.
- **Asynchronous Event-Driven Notifications**: Events (task assigned, comment added) are handled by queued listeners (`ShouldQueue`) that create database notifications in the background through a database queue worker.
- **Reports & Data Exports**:
  - Analytics endpoint returning completion rates, status distribution, and task counts.
  - Export project summaries to **Excel** (`.xlsx`) and **PDF** (`.pdf`) formats.
- **Interactive OpenAPI / Swagger Docs**: Annotated API documentation powered by `L5-Swagger`, with bearer authentication testing in the browser.
- **Automated Testing Suite**: 51 unit and feature tests (PHPUnit with `RefreshDatabase`) covering authentication, authorization, notifications, queued listeners, and report exports.
- **Dockerized Setup**: Docker Compose environment with PHP-FPM, Nginx, MySQL, and a queue worker.

---

## Tech Stack

- **Framework**: Laravel 11.x (PHP 8.2+)
- **Authentication**: JWT (`tymon/jwt-auth`)
- **Database**: MySQL (SQLite for testing)
- **Architecture**: Service Layer Pattern & Policy-Based Authorization
- **Queue System**: Database queue with a dedicated worker
- **Exports**: `maatwebsite/excel`, `barryvdh/laravel-dompdf`
- **Documentation**: `darkaonline/l5-swagger` (OpenAPI 3.0)
- **Testing**: PHPUnit
- **Containerization**: Docker, Docker Compose, Nginx

---

## Quick Start & Setup

### Option 1: Docker (recommended)

Requirements: Docker and Docker Compose.

```bash
git clone https://github.com/Yazan265/task-craft-api.git
cd task-craft-api
cp .env.example .env
docker compose up -d --build
```

On the first start, the `app` container installs the Composer dependencies.
Follow the progress with `docker compose logs -f app` and wait until it finishes
and PHP-FPM reports that it is ready. Then run:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
docker compose exec app php artisan l5-swagger:generate
docker compose restart queue
```

The API is available at `http://localhost:8000/api`.

| Service     | Description                              | Port |
| :---------- | :--------------------------------------- | :--- |
| `app`       | Laravel on PHP 8.2-FPM                   | -    |
| `webserver` | Nginx                                    | 8000 |
| `db`        | MySQL 8.0                                | 3307 |
| `queue`     | Queue worker (`php artisan queue:work`)  | -    |

> The database credentials in `docker-compose.yml` are for local development only.
> Inside Docker, the database settings are provided by `docker-compose.yml`, so `DB_HOST` in `.env` does not need to change.

### Option 2: Local environment

```bash
git clone https://github.com/Yazan265/task-craft-api.git
cd task-craft-api
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan storage:link
php artisan l5-swagger:generate
php artisan queue:work   # run in a separate terminal
php artisan serve
```

---

## Environment Variables Configuration

Make sure your `.env` contains the essential database, queue, JWT, and Swagger settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_craft_db
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

JWT_SECRET=your_generated_jwt_secret_here
JWT_TTL=60
L5_SWAGGER_GENERATE_ALWAYS=true
```

---

## Interactive API Documentation

Once the application is running, open the Swagger UI to explore and test the endpoints:

`http://127.0.0.1:8000/api/documentation`

---

## API Endpoints Summary

### Authentication (`/api`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| POST | /api/register | Register a new user account | No |
| POST | /api/login | Authenticate user & return JWT token | No |
| POST | /api/refresh | Refresh an existing JWT token | Yes |
| GET | /api/me | Fetch authenticated user profile | Yes |
| POST | /api/logout | Logout user (invalidate token) | Yes |

### Projects & Members (`/api/projects`)
| Method | Endpoint | Description | Auth / Permission |
| :--- | :--- | :--- | :--- |
| GET | /api/projects | List user's active projects | Yes |
| POST | /api/projects | Create a new project | Yes |
| GET | /api/projects/{project} | Get detailed project information | Owner / Member |
| PUT | /api/projects/{project} | Update project details | Project Owner |
| DELETE | /api/projects/{project} | Delete project and associated resources | Project Owner |
| POST | /api/projects/{project}/members | Add a new member with role (`member`/`viewer`) | Project Owner |
| DELETE | /api/projects/{project}/members/{user} | Remove a member from project | Project Owner |

### Tasks (`/api`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| GET | /api/projects/{project}/tasks | List project tasks (supports `?status=`, `?priority=`, `?search=`) | Yes |
| POST | /api/projects/{project}/tasks | Create task & trigger async assignment notification | Yes |
| GET | /api/tasks/{id} | Get task details | Yes |
| PUT | /api/tasks/{id} | Update task status, priority, or assignee | Yes |
| DELETE | /api/tasks/{id} | Delete task | Yes |

### Comments & Attachments (`/api`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| GET | /api/tasks/{task}/comments | List task comments and attachments | Yes |
| POST | /api/tasks/{task}/comments | Add comment with optional file attachment (restricted on `completed` tasks) | Yes |
| PUT | /api/comments/{comment} | Update comment text | Yes |
| DELETE | /api/comments/{comment} | Delete comment | Yes |

### Reports & Exports (`/api/projects/{project}/reports`)
| Method | Endpoint | Description | Format | Auth Required |
| :--- | :--- | :--- | :--- | :---: |
| GET | /api/projects/{project}/reports/summary | Get project analytics and completion metrics | JSON | Yes |
| GET | /api/projects/{project}/reports/export/excel | Export project report to Excel | .xlsx | Yes |
| GET | /api/projects/{project}/reports/export/pdf | Export project report to PDF | .pdf | Yes |

### Notifications (`/api/notifications`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| GET | /api/notifications | List authenticated user's notifications | Yes |
| GET | /api/notifications/unread-count | Fetch total count of unread notifications | Yes |
| PATCH | /api/notifications/read-all | Mark all notifications as read | Yes |
| PATCH | /api/notifications/{id}/read | Mark a specific notification as read | Yes |

---

## Running Automated Tests

Run the full test suite:

```bash
# Local
php artisan test

# Docker
docker compose exec app php artisan test
```

Run specific test classes:

```bash
php artisan test --filter=TaskFilterTest
php artisan test --filter=ProjectReportTest
php artisan test --filter=ProjectMemberTest
php artisan test --filter=NotificationTest
```

---

## Architecture & Best Practices

- **Service Layer Pattern**: Keeps business logic out of the controllers.
- **Asynchronous Queue Workers**: Event listeners implement `ShouldQueue`, so notifications are created in the background without delaying the HTTP response.
- **Policy Authorization**: Resource access is controlled with Laravel Policies based on `project_user` roles.
- **Standardized API Responses**: Eloquent API Resources provide a consistent JSON structure.
- **Test Isolation**: Feature tests use `RefreshDatabase` for a clean database state.

---

## Author

Developed by **Yazan Loho** — Backend Developer (Laravel / PHP).