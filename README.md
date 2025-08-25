# VSU OBE Backend API

A comprehensive RESTful API built with Laravel for managing Outcome-Based Education (OBE) systems. This API serves as the backend for the [VSU OBE Frontend](https://github.com/Orekidesu/vsu-obe-frontend) and provides a complete solution for academic institutions to manage their OBE requirements, curriculum mapping, and assessment processes.

## Features

• **Role-based Access Control** - Admin, Department, Faculty Member, and Dean roles with specific permissions

• **Program Management** - Create and manage academic programs with proposals and revisions

• **Curriculum Management** - Comprehensive curriculum design with course mapping and outcomes

• **OBE Mapping System** - Map Graduate Attributes (GA), Program Educational Objectives (PEO), and Program Outcomes (PO)

• **Course Outcome Management** - Define course outcomes with ABCD format and CPA domains

• **TLA Integration** - Teaching, Learning, and Assessment task management

• **Committee Workflow** - Faculty committee assignments and revision management

• **Mission & Vision Alignment** - Link educational objectives to institutional mission and vision

• **RESTful API Endpoints** - Clean API architecture with versioning support

• **Authentication & Authorization** - Laravel Sanctum for secure API access

## Prerequisites

Before running this project, make sure you have the following installed:

• **PHP 8.1 or higher**

• **Composer** - [Download here](https://getcomposer.org/)

• **MySQL 5.7+ or MariaDB 10.3+**

• **Node.js 16+ and npm** (for asset compilation)

• **Git**

### Recommended Development Environment

• **Laragon (Windows)** - [Download here](https://laragon.org/)

• **XAMPP (Cross-platform)** - [Download here](https://www.xampp.org/)

• **Laravel Valet (macOS)** - [Installation guide](https://laravel.com/docs/valet)

## Installation

1. **Clone the repository**

```bash
git clone https://github.com/Orekidesu/vsu_obe_backend.git
cd vsu_obe_backend
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Copy environment file**

```bash
copy .env.example .env
```

4. **Generate application key**

```bash
php artisan key:generate
```

5. **Configure database**

Edit the `.env` file with your database credentials:

```env
FRONTEND_URL=http://localhost:3000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vsu_obe_backend
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Sanctum Configuration for SPA
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

6. **Create database**

Create a new database named `vsu_obe_backend` in your MySQL server.

7. **Run database migrations and seeders**

```bash
php artisan migrate --seed
```

## Running the Application

1. **Start the development server**

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

2. **For production deployment**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 📁 Project Structure

```
vsu_obe_backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── Admin/                   # Admin management controllers
│   │   │           │   ├── UserController.php
│   │   │           │   ├── RoleController.php
│   │   │           │   ├── DepartmentController.php
│   │   │           │   ├── FacultyController.php
│   │   │           │   ├── MissionController.php
│   │   │           │   ├── VisionController.php
│   │   │           │   └── GraduateAttributeController.php
│   │   │           ├── Department/              # Department-level controllers
│   │   │           │   ├── ProgramController.php
│   │   │           │   ├── ProgramProposalController.php
│   │   │           │   ├── CurriculumController.php
│   │   │           │   ├── CourseController.php
│   │   │           │   ├── ProgramOutcomeController.php
│   │   │           │   ├── ProgramEducationalObjectiveController.php
│   │   │           │   └── ...
│   │   │           ├── Faculty/                 # Faculty-level controllers
│   │   │           │   ├── CourseDetailsWizardController.php
│   │   │           │   ├── CommitteeRevisionController.php
│   │   │           │   └── ...
│   │   │           ├── Dean/                    # Dean approval controllers
│   │   │           │   └── ProposalReviewController.php
│   │   │           └── Shared/                  # Shared controllers
│   │   │               └── CurriculumCoursePOController.php
│   │   ├── Middleware/                          # Custom middleware
│   │   ├── Requests/                            # Form request validation
│   │   └── Resources/                           # API resources
│   │       └── Api/V1/
│   │           ├── Admin/
│   │           ├── Department/
│   │           ├── Faculty/
│   │           └── Shared/
│   ├── Models/                                  # Eloquent models
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Program.php
│   │   ├── ProgramProposal.php
│   │   ├── Curriculum.php
│   │   ├── Course.php
│   │   ├── CourseOutcome.php
│   │   ├── ProgramOutcome.php
│   │   ├── ProgramEducationalObjective.php
│   │   ├── GraduateAttribute.php
│   │   ├── Mission.php
│   │   ├── Vision.php
│   │   ├── Committee.php
│   │   └── ...
│   ├── Rules/                                   # Custom validation rules
│   └── Providers/                               # Service providers
├── database/
│   ├── migrations/                              # Database migrations
│   ├── seeders/                                 # Database seeders
│   └── factories/                               # Model factories
├── routes/
│   ├── api.php                                  # Main API routes
│   └── api/                                     # Versioned API routes
│       └── v1/
│           ├── auth.php                         # Authentication routes
│           ├── role.php                         # Role-based routes
│           └── user.php                         # User routes
├── storage/                                     # Application storage
├── tests/                                       # Application tests
├── vendor/                                      # Composer dependencies
├── .env.example                                 # Environment variables template
├── artisan                                      # Laravel Artisan CLI
├── composer.json                                # PHP dependencies & scripts
└── README.md                                    # Project documentation
```

### Key Directories Explained

• `app/Http/Controllers/Api/V1/` - Versioned API controllers organized by role (Admin, Department, Faculty, Dean)

• `app/Models/` - Eloquent models representing OBE entities (Programs, Outcomes, Curricula, etc.)

• `app/Http/Resources/` - API resource transformers for consistent JSON responses

• `database/migrations/` - Database schema migrations for OBE system tables

• `routes/api/v1/` - API route definitions with role-based access control

• `app/Rules/` - Custom validation rules for OBE-specific requirements

### API Architecture

• **RESTful Design** - Following REST conventions for consistent API endpoints

• **Version Control** - API versioning (v1) for backward compatibility

• **Laravel Sanctum** - Token-based authentication for SPA applications

• **Role-based Access** - Multi-tier access control (Admin, Department, Faculty, Dean)

• **Resource Controllers** - Standard CRUD operations for all entities

• **Middleware Protection** - Authentication and role authorization on protected endpoints

## API Endpoints

### Authentication

| Method | Endpoint           | Description            |
| ------ | ------------------ | ---------------------- |
| POST   | `/api/v1/register` | Register a new user    |
| POST   | `/api/v1/login`    | Login user             |
| POST   | `/api/v1/logout`   | Logout user            |
| GET    | `/api/v1/user`     | Get authenticated user |

### Admin Routes (`/api/v1/admin/`)

| Method              | Endpoint               | Description                    |
| ------------------- | ---------------------- | ------------------------------ |
| GET                 | `/roles`               | Get all user roles             |
| GET/POST/PUT/DELETE | `/visions`             | Manage institutional visions   |
| GET/POST/PUT/DELETE | `/missions`            | Manage institutional missions  |
| GET/POST/PUT/DELETE | `/users`               | User management                |
| GET/POST/PUT/DELETE | `/departments`         | Department management          |
| GET/POST/PUT/DELETE | `/faculties`           | Faculty management             |
| GET/POST/PUT/DELETE | `/graduate-attributes` | Graduate attributes management |

### Department Routes (Department Role)

| Method              | Endpoint                          | Description                 |
| ------------------- | --------------------------------- | --------------------------- |
| GET/POST/PUT/DELETE | `/programs`                       | Program management          |
| GET/POST/PUT/DELETE | `/program-proposals`              | Program proposal management |
| GET/POST/PUT/DELETE | `/curriculums`                    | Curriculum management       |
| GET/POST/PUT/DELETE | `/courses`                        | Course management           |
| GET/POST/PUT/DELETE | `/program-outcomes`               | Program outcome management  |
| GET/POST/PUT/DELETE | `/program-educational-objectives` | PEO management              |
| GET/POST/PUT/DELETE | `/course-categories`              | Course category management  |
| GET/POST/PUT/DELETE | `/semesters`                      | Semester management         |

### Faculty Routes (Faculty Role)

| Method  | Endpoint                 | Description                   |
| ------- | ------------------------ | ----------------------------- |
| POST    | `/course-details-wizard` | Course outcome wizard         |
| GET/PUT | `/committee-revisions`   | Committee revision management |

### Shared Routes

| Method              | Endpoint                                   | Description                   |
| ------------------- | ------------------------------------------ | ----------------------------- |
| GET                 | `/curriculum-course/{id}/program-outcomes` | Get POs for curriculum course |
| GET/POST/PUT/DELETE | `/revisions`                               | Revision management           |

### Health Check

| Method | Endpoint      | Description       |
| ------ | ------------- | ----------------- |
| GET    | `/api/health` | API health status |

## Frontend Application

This API is designed to work with the Vue.js frontend application:

🔗 [VSU OBE Vue.js Frontend](https://github.com/Orekidesu/vsu-obe-frontend)

## OBE System Overview

This system implements a complete Outcome-Based Education framework:

### Core Concepts

-   **Graduate Attributes (GA)** - 8 fundamental attributes aligned with institutional standards
-   **Program Educational Objectives (PEO)** - High-level career and professional practice outcomes
-   **Program Outcomes (PO)** - Specific knowledge, skills, and behaviors students acquire
-   **Course Outcomes (CO)** - Learning outcomes for individual courses

### Mapping Relationships

-   **Mission ↔ PEO** - Educational objectives aligned with institutional mission
-   **PEO ↔ GA** - Objectives mapped to graduate attributes
-   **PO ↔ GA** - Program outcomes mapped to graduate attributes
-   **PO ↔ PEO** - Program outcomes supporting educational objectives
-   **CO ↔ PO** - Course outcomes contributing to program outcomes

### Assessment Framework

-   **ABCD Format** - Course outcomes defined with Audience, Behavior, Condition, Degree
-   **CPA Domains** - Cognitive, Psychomotor, Affective learning domains
-   **TLA Tasks** - Teaching, Learning, Assessment activities
-   **IED Mapping** - Introduced, Emphasized, Demonstrated levels

## Some Considerations

This API uses Laravel Sanctum for authentication. Make sure to:

1. Configure CORS settings for your frontend domain
2. Set up SANCTUM_STATEFUL_DOMAINS in your `.env` file
3. Use the `/api/v1/login` endpoint to authenticate users
4. Include authentication tokens in subsequent requests
5. Ensure proper role-based access is configured

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint
```

### Database Reset

```bash
php artisan migrate:fresh --seed
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request
