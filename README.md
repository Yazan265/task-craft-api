# TaskCraft API 

A robust Task & Project Management RESTful API built with **Laravel 11**, focusing on high code quality, **Test-Driven Development (TDD)**, Asynchronous Job Queues, JWT Authentication, Clean Architecture (Service Layer Pattern), OpenAPI/Swagger documentation, and multi-format report exports (PDF & Excel).

---

## Key Features

- **JWT Authentication**: Secure user registration, login, token refresh, and logout using JSON Web Tokens (`tymon/jwt-auth`).
- **Project Management & Access Control**: Full CRUD operations for managing projects with thin controllers, dedicated `ProjectService`, and custom Form Requests.
- **Project Members & Granular Roles**: Multi-user collaboration using the `project_user` pivot table supporting fine-grained role permissions (`owner`, `member`, `viewer`).
- **Task Management & Advanced Filtering**: Create, assign, and track tasks with built-in Eloquent scopes for filtering by status (`?status=`), priority (`?priority=`), and text search (`?search=`).
- **Strict Business Logic & Restrictions**: Enforced rules including preventing new comments on `completed` tasks and restricting project modification solely to project owners.
- **File Uploads & Task Comments**: Threaded task comments with file attachment uploads, automatic storage management, and dynamic `attachment_url` accessors.
- **Asynchronous Event-Driven Architecture**: Decoupled events (`TaskAssigned`) and database notifications handled via **Asynchronous Queues** (`ShouldQueue`) for scalable background job processing.
- **Reports & Data Exports**: 
  - Analytics endpoint returning real-time completion rates, status distribution, and task counts.
  - Export comprehensive project summaries directly to **Excel** (`.xlsx`) and **PDF** (`.pdf`) formats.
- **Interactive OpenAPI / Swagger Docs**: Fully annotated API documentation powered by `L5-Swagger` with support for bearer authentication testing directly in-browser.
- **Automated Testing Suite**: 100% Core Feature coverage using PHPUnit with `RefreshDatabase` TDD methodology (50+ passing tests).

---

## Tech Stack

- **Framework**: Laravel 11.x (PHP 8.2+)
- **Authentication**: JWT (`tymon/jwt-auth`)
- **Database**: MySQL / SQLite (for testing)
- **Architecture**: Service Layer Pattern & Policy-Based Authorization
- **Queue System**: Database Asynchronous Queue Worker
- **Exports**: `maatwebsite/excel`, `barryvdh/laravel-dompdf`
- **Documentation**: `darkaonline/l5-swagger` (OpenAPI 3.0)
- **Testing**: PHPUnit

---

## Quick Start & Setup

### Local Environment Setup:
1. Clone the repository:
   git clone [https://github.com/Yazan265/task-craft-api.git](https://github.com/Yazan265/task-craft-api.git)
   cd task-craft-api
2. Install Dependencies:
   composer install
3. Environment Configuration:
   cp .env.example .env
4. Generate App Key & JWT Secret:
   php artisan key:generate
   php artisan jwt:secret
5. Run Database Migrations & Seeders:
   php artisan migrate --seed
6. Create Storage Link for File Uploads:
   php artisan storage:link
7. Run Queue Worker for Asynchronous Jobs:
   php artisan queue:work
8. Generate Swagger Documentation:
   php artisan l5-swagger:generate
9. Run Local Development Server:
   php artisan serve

---

### Docker Setup (Containerized):
1. Build & Start Containers:
   docker-compose build --no-cache app
   docker-compose up -d
2. Run Commands Inside Docker Container:
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan jwt:secret
   docker-compose exec app php artisan migrate --seed
   docker-compose exec app php artisan storage:link
   docker-compose exec app php artisan l5-swagger:generate

---

## Environment Variables Configuration

Ensure your `.env` contains the essential database, queue, JWT, and Swagger settings:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1 (or 'db' for Docker)
DB_PORT=3306
DB_DATABASE=task_craft_db
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

JWT_SECRET=your_generated_jwt_secret_here
JWT_TTL=60
L5_SWAGGER_GENERATE_ALWAYS=true

---

## Interactive API Documentation

Once the application is running, access the interactive **Swagger UI** to explore and test endpoints:

  `[http://127.0.0.1:8000/api/documentation](http://127.0.0.1:8000/api/documentation)`

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
| GET | /api/projects/{project}/tasks | List project tasks (Supports `?status=`, `?priority=`, `?search=`) | Yes |
| POST | /api/projects/{project}/tasks | Create task & trigger async assignment notification | Yes |
| GET | /api/tasks/{id} | Get task details | Yes |
| PUT | /api/tasks/{id} | Update task status, priority, or assignee | Yes |
| DELETE | /api/tasks/{id} | Delete task | Yes |

### Comments & Attachments (`/api`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| GET | /api/tasks/{task}/comments | List task comments and attachments | Yes |
| POST | /api/tasks/{task}/comments | Add comment with optional file attachment (Restricted on `completed` tasks) | Yes |
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

This project adheres strictly to **Test-Driven Development (TDD)** using PHPUnit.

Run the full test suite (Local):
php artisan test

Run tests using Docker:
docker-compose exec app php artisan test

Run specific test groups or classes:
php artisan test --filter=TaskFilterTest
php artisan test --filter=ReportTest
php artisan test --filter=ProjectMemberTest
php artisan test --filter=NotificationTest

---

## Architecture & Best Practices

**Service Layer Pattern**: Decouples complex business calculations (reports, exports, file handling) from Controllers.
**Asynchronous Queue Workers**: Uses `ShouldQueue` on event listeners to offload database notification dispatching without delaying HTTP response cycles.
**Policy Authorization**: Resource access is strictly controlled using Laravel Policies based on `project_user` roles.
**Standardized API Responses**: Built with Eloquent API Resources to unify JSON output structure and handle relational data efficiently.
**TDD Methodology**: Feature tests drive domain execution and database isolation via `RefreshDatabase`.

---

## Author & License

Developed with by **Yazan Loho** — Software Engineer & Backend Developer.