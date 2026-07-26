# Learning Management System (LMS) API

A RESTful API for an online learning platform built with **Laravel 12** and **Laravel Sanctum**. The system supports three user roles (student, instructor, admin) and covers the full lifecycle of an online course: creation, content structuring, enrollment, progress tracking, quizzes, reviews, and certification.

Work in Progress — This project is still under active development. Planned additions include: Redis (caching/queues), payment gateway integration, Docker setup for local development and deployment, and further localization coverage. Contributions and structure may change as these are added.

---

## Tech Stack

| Component | Technology |
|---|---|
| Framework | Laravel 12 |
| Authentication | Laravel Sanctum (Bearer Token) |
| Database | PostgreSQL |
| Primary Keys | UUID (across all tables) |
| API Style | RESTful JSON API |

---

## Core Features

### User Management
- Registration, login, logout (token-based via Sanctum)
- Email verification
- Password reset / change password
- Profile management (bio, phone, profile image)
- Three roles: `user` (student), `instructor`, `admin`
- Account activation status (`is_active`)

### Course Catalog
- **Categories** — course categories with slugs, icons, active status
- **Courses** — owned by an instructor, linked to a category, with price, level, thumbnail, and preview video
- **Sections** — ordered content groups within a course
- **Lessons** — ordered lessons within a section (video URL + text content, preview flag)

### Enrollment & Progress
- Students enroll in published courses
- Per-lesson watch/completion tracking (`Progress`)
- Automatic course progress percentage calculation based on completed lessons
- Automatic enrollment completion detection

### Quizzes & Assessments
- One quiz per section, with configurable passing score, time limit, and allowed attempts
- Multiple-choice questions with one correct answer per question
- **Section locking**: a student cannot access a section until they pass the quiz of the previous section
- Quiz attempts are graded server-side; correct answers are never exposed to the client before submission

### Reviews
- One review (rating + comment) per student per course
- Restricted to enrolled students only
- On-demand rating average/count computation

### Certificates
- Automatically issued when a student completes 100% of a course
- Unique, human-readable certificate numbers (e.g. `CERT-2026-A1B2C3D4`)
- Public certificate verification endpoint (no authentication required)

### Localization
- Full English/Arabic support for API response messages and validation errors
- Language selected via the `Accept-Language` request header

---

## Architecture

The codebase follows a layered architecture to keep controllers thin and business logic reusable and testable.

```
Request → FormRequest (validation) → Controller → Service (business logic) → Model
                                          ↓
                                      Resource (response shaping)
```

| Layer | Responsibility |
|---|---|
| **Form Requests** | Input validation and request-level authorization |
| **Controllers** | HTTP orchestration only — delegates to Services, formats responses via `ApiResponse` trait |
| **Services** | All business logic, queries, and side effects (e.g. slug generation, progress recalculation, certificate issuance) |
| **Policies** | Ownership/authorization checks (e.g. "is this instructor the owner of this course?") |
| **Resources** | Shape and control what data is exposed in API responses |

### Key Design Decisions

- **`CoursePolicy` is reused** across `Course`, `Section`, `Lesson`, `Quiz`, and `Question` authorization checks (ownership is always derived from the parent course), avoiding a separate policy per entity.
- **Route Model Binding with `scopeBindings()`** is used on all nested resource routes (e.g. `courses/{course}/sections/{section}/lessons/{lesson}`) to guarantee that child resources actually belong to their parent in the URL — preventing cross-resource ID tampering.
- **Two separate Question resources** exist by design:
  - `QuestionResource` — never exposes the correct answer (used while a student is taking a quiz)
  - `QuestionWithAnswerResource` — exposes correct answers (used for instructor management and post-submission review)
- **Certificates are never created directly via an endpoint.** They are issued automatically by `CertificateService` when `ProgressService` detects a course has reached 100% completion.

---

## Database Schema Overview

All tables use **UUID primary keys** and (where applicable) `SoftDeletes`.

---

## Authentication & Authorization

- **Sanctum Bearer Tokens** — all protected endpoints require an `Authorization: Bearer {token}` header.
- **Roles** (`user`, `instructor`, `admin`) gate access to management endpoints (e.g. only instructors/admins can create courses; only admins can manage categories).
- **Ownership checks** (via Policies or manual `authorizeOwnership()` helpers) ensure users can only modify their own resources (their courses, their enrollments, their reviews, etc.), regardless of role.

---

## Middleware

| Alias | Purpose |
|---|---|
| `auth:sanctum` | Authenticates the request via Bearer token |
| `active` | Blocks access if the user's account has been deactivated |
| `role:{role}` | Restricts access to a specific role (e.g. `role:admin`) |
| `verified` | Requires a verified email address (applied to sensitive actions like enrollment) |
| `throttle:{max},{minutes}` | Rate limits sensitive endpoints (login, register, password reset, quiz attempts, reviews, certificate verification) |

Registered in `bootstrap/app.php`:

---

## Localization

The API responds in English or Arabic based on the `Accept-Language` request header:

```
Accept-Language: ar
```
---

## Installation

```bash
# Clone and install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your .env — database, mail, frontend URL, etc.
# DB_CONNECTION=pgsql
# FRONTEND_URL=http://localhost:3000

# Run migrations
php artisan migrate

# (Optional) seed the database
php artisan db:seed

# Serve the application
php artisan serve
```

### Required `.env` values

| Key | Notes |
|---|---|
| `APP_DEBUG` | Must be `false` in production |
| `APP_ENV` | `local` for development, `production` for deployment |
| `DB_CONNECTION` | `pgsql` |
| `FRONTEND_URL` | Used for CORS configuration |
| `MAIL_MAILER` | `log` for local dev, real SMTP driver for production |

---
