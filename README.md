# TaskCraft API 

A robust Task & Project Management RESTful API built with **Laravel 11**, focusing on high code quality, **Test-Driven Development (TDD)**, JWT Authentication, and Clean Architecture (Service Layer Pattern).

---

##  Key Features

- **JWT Authentication**: Secure user registration, login, and token management using JSON Web Tokens.
- **Project Management**: CRUD operations for managing projects.
- **Project Members & Roles**: Multi-user collaboration with role-based permissions (`owner`, `member`, `viewer`).
- **Task Management**: Create, assign, and track task statuses within projects.
- **Authorization**: Policy & Gate protection ensuring non-members cannot access private resources.
- **Automated Testing**: 100% Feature-tested using PHPUnit / Pest with TDD methodology.

---

##  Tech Stack

- **Framework**: Laravel 11.x
- **Language**: PHP 8.2+
- **Authentication**: JWT (Tymon / JWT-Auth)
- **Database**: MySQL / SQLite (for testing)
- **Testing**: PHPUnit

---

##  Quick Start & Setup

### 1. Clone the repository
`git clone https://github.com/Yazan265/task-craft-api.git`
`cd task-craft-api`

### 2. Install Dependencies
`composer install`

### 3. Environment Configuration
Copy `.env.example` to `.env` and set up your environment variables:
`cp .env.example .env`

### 4. Generate App Key & JWT Secret
`php artisan key:generate`
`php artisan jwt:secret`

### 5. Run Database Migrations
`php artisan migrate`

---

##  Environment Variables Configuration

Make sure your `.env` contains the essential JWT and Database settings:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_craft_db
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=your_generated_jwt_secret_here
JWT_TTL=60

---

##  API Endpoints Documentation

###  Authentication
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `POST` | `/api/register` | Register a new user | No |
| `POST` | `/api/login` | Authenticate user & return JWT Token | No |


###  Projects
| Method | Endpoint | Description | Role / Permission |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/projects` | List all available projects |  Authenticated |
| `POST` | `/api/projects` | Create a new project |  Authenticated |
| `GET` | `/api/projects/{id}` | Get project details |  Owner / Member |
| `PUT` | `/api/projects/{id}` | Update project details |  Project Owner |
| `DELETE` | `/api/projects/{id}` | Delete project |  Project Owner |

###  Project Members
| Method | Endpoint | Description | Role / Permission |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/projects/{project}/members` | Add a member to project |  Project Owner |
| `DELETE` | `/api/projects/{project}/members/{user}` | Remove a member |  Project Owner |

###  Tasks
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/projects/{project}/tasks` | Get project tasks |  Yes |
| `POST` | `/api/projects/{project}/tasks` | Create task in project |  Yes |
| `GET` | `/api/tasks/{id}` | Get task details |  Yes |
| `PUT` | `/api/tasks/{id}` | Update task |  Yes |
| `DELETE` | `/api/tasks/{id}` | Delete task |  Yes |

###  Comments
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/tasks/{task}/comments` | List all comments for a specific task |  Yes |
| `POST` | `/api/tasks/{task}/comments` | Add a new comment to a task |  Yes |
| `PUT` | `/api/comments/{comment}` | Update an existing comment |  Yes |
| `DELETE` | `/api/comments/{comment}` | Delete a comment |  Yes |

###  Notifications
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/notifications` | List all user notifications |  Yes |
| `PATCH` | `/api/notifications/read-all` | Mark all notifications as read |  Yes |
| `PATCH` | `/api/notifications/{id}/read` | Mark a single notification as read |  Yes |


---

##  Running Automated Tests

This project adheres strictly to **Test-Driven Development (TDD)** using PHPUnit.

Run the full test suite:
`php artisan test`

Run feature-specific tests:
`php artisan test --filter=ProjectMemberTest`

---

##  Architecture Overview

The application follows the **Controller - Service Layer Pattern** to ensure thin controllers, business logic isolation, and high testability:

- **Controllers**: Handle HTTP Requests and JSON responses (`App\Http\Controllers\API`).
- **Services**: Encapsulate core business logic (`App\Services`).
- **Policies**: Manage fine-grained resource authorization (`App\Policies`).
- **Tests**: Comprehensive integration & feature tests (`tests/Feature`).

---

##  License & Author

Developed by **Yazan** - Software Engineer & Backend Developer.