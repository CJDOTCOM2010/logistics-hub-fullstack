# Backend Structure Document

This document provides a clear overview of the backend architecture, database management, API design, hosting, infrastructure, security, and maintenance strategies for the Logistics Delivery Management System.

## 1. Backend Architecture

Overall Structure:
- The backend is built as a **modular monolith** using **Laravel (PHP)**. Each major feature (Shipments, Users, Accounting, HR) lives in its own module with controllers, services, and models.
- Real-time features (live tracking, notifications, chat) are handled by a separate **Node.js** service communicating via **Redis**.

Key Design Patterns & Frameworks:
- **Service-Repository Pattern**: Separates database access (repositories) from business logic (services) for cleaner, testable code.
- **Eloquent ORM**: Simplifies database interactions in Laravel.
- **Blade Templates / Inertia.js / Livewire**: Powers the frontend of the Laravel app, allowing dynamic single-page-app behaviors without an entirely separate frontend codebase.
- **spatie/laravel-permission**: Provides a robust, database-driven role-based access control (RBAC) system.

Scalability, Maintainability & Performance:
- **Modularity** makes it easy to add or remove features.
- **Database-driven configuration** (settings stored in the database) allows instant, system-wide UI and behavior changes via the Super Admin panel.
- **Redis** is used for caching and as a message broker to decouple Laravel and Node.js.
- Background jobs and queues (Laravel Queues with Redis) keep heavy tasks off request threads, improving responsiveness.

## 2. Database Management

Technologies & Types:
- Primary data store: **MySQL** (relational).
- Caching & messaging: **Redis**.

Data Structure & Access:
- All core entities (Users, Roles, Permissions, Shipments, Settings) are tables in MySQL, with Eloquent models mapping to them.
- Redis is used for:
  - Caching frequent queries (e.g., shipment lookup).
  - Pub/Sub between Laravel and Node.js for real-time events.

Best Practices:
- Schema versioning via Laravel **migrations** ensures reproducible changes.
- **Seeders** and **factories** allow test data generation and consistent staging environments.
- Regular backups of MySQL and Redis snapshots.

## 3. Database Schema

Human-Readable Table Descriptions:
- **users**: Stores user accounts (id, name, email, password, role_id, KYC status, timestamps).
- **roles**: Defines roles (Super Admin, Admin, Driver, Customer, Accountant, HR, Agent).
- **permissions**: Fine-grained actions (create-shipment, view-report).
- **role_has_permissions**: Links roles to permissions.
- **model_has_roles** / **model_has_permissions**: Links users to roles/permissions.
- **shipments**: Shipment records (id, customer_id, driver_id, pickup_address, delivery_address, status_id, assigned_at, delivered_at, timestamps).
- **shipment_statuses**: Status definitions (Pending, In Transit, Delivered).
- **locations**: Geolocation logs (id, shipment_id, latitude, longitude, recorded_at).
- **notifications**: In-app notifications (id, user_id, type, payload, read_at, timestamps).
- **messages**: Chat messages (id, sender_id, receiver_id, content, sent_at).
- **system_settings**: Key-value pairs for global UI or feature toggles.

Example MySQL Schema (simplified):
```
CREATE TABLE users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  password VARCHAR(255),
  kyc_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE,
  guard_name VARCHAR(50)
);

CREATE TABLE permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE,
  guard_name VARCHAR(50)
);

CREATE TABLE role_has_permissions (
  role_id INT,
  permission_id INT,
  PRIMARY KEY(role_id, permission_id)
);

CREATE TABLE model_has_roles (
  role_id INT,
  model_type VARCHAR(50),
  model_id BIGINT,
  PRIMARY KEY(role_id, model_type, model_id)
);

CREATE TABLE shipments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT,
  driver_id BIGINT NULL,
  pickup_address TEXT,
  delivery_address TEXT,
  status_id INT,
  assigned_at TIMESTAMP NULL,
  delivered_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE shipment_statuses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50)
);

CREATE TABLE locations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  shipment_id BIGINT,
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  recorded_at TIMESTAMP
);

CREATE TABLE notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  type VARCHAR(50),
  payload JSON,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP
);

CREATE TABLE messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT,
  receiver_id BIGINT,
  content TEXT,
  sent_at TIMESTAMP
);

CREATE TABLE system_settings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  key VARCHAR(100) UNIQUE,
  value TEXT,
  updated_at TIMESTAMP
);
```

## 4. API Design and Endpoints

Approach:
- **RESTful**, versioned (e.g., `/api/v1/`).
- Token-based authentication via **Laravel Sanctum** or **Passport**.
- Rate limiting and input validation on all endpoints.

Key Endpoints:
- **Auth & Users**:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/register`
  - `GET /api/v1/users` (RBAC-controlled)
  - `PUT /api/v1/users/{id}`
- **Roles & Permissions**:
  - `GET /api/v1/roles`
  - `POST /api/v1/roles`
  - `GET /api/v1/permissions`
- **Shipments**:
  - `GET /api/v1/shipments`
  - `POST /api/v1/shipments`
  - `GET /api/v1/shipments/{id}`
  - `PUT /api/v1/shipments/{id}`
- **Tracking & Locations**:
  - `GET /api/v1/shipments/{id}/locations`
- **Notifications & Chat**:
  - `GET /api/v1/notifications`
  - `POST /api/v1/chat/messages`
- **Settings & Modules**:
  - `GET /api/v1/settings`
  - `PUT /api/v1/settings/{key}`

All endpoints return JSON and adhere to consistent success/error structures.

## 5. Hosting Solutions

Primary Cloud Provider:
- **AWS** (could be swapped for Azure or GCP). 

Components:
- **EC2 Auto Scaling Group**: Hosts the Laravel app and Node.js service in separate instances or containers.
- **RDS (MySQL)**: Managed database service with automated backups and multi-AZ setups.
- **ElastiCache (Redis)**: Managed caching and Pub/Sub.
- **S3**: Object storage for file uploads (e.g., KYC documents) with lifecycle policies.
- **CloudFront**: CDN for static assets (JS, CSS, images).
- **Route 53**: DNS management.

Benefits:
- High availability via multi-AZ deployments.
- Automatic scaling under load.
- Predictable, usage-based costs.

## 6. Infrastructure Components

Load Balancing:
- **AWS Application Load Balancer (ALB)** distributes traffic across multiple Laravel and Node.js instances.

Caching & Queueing:
- **Redis ElastiCache**:
  - Caching frequent DB queries.
  - Pub/Sub for real-time events.
  - Laravel Queues for background jobs (emails, reports).

CDN:
- **CloudFront** speeds up static asset delivery worldwide.

Other Components:
- **SMTP Service** (e.g., SES, Mailgun) for transactional emails.
- **SSL/TLS Certificates** via AWS Certificate Manager.
- **Containerization** (optional) using Docker and ECS/EKS.

## 7. Security Measures

Authentication & Authorization:
- **Laravel Sanctum/Passport** for API tokens.
- **spatie/laravel-permission** for RBAC.
- Mandatory **2FA** for high-privilege users.
- **IP whitelisting** for the Super Admin panel.

Data Protection:
- **TLS** for all in-transit data.
- **AES-256 encryption** at rest for KYC documents and sensitive data in S3.
- Input validation and Eloquent parameter binding guard against SQL injection.
- CSRF protection on all form submissions.

Audit & Compliance:
- **Audit logs** of all admin actions.
- Regular security scans and dependency updates.
- GDPR-compatible data retention and deletion policies.

## 8. Monitoring and Maintenance

Monitoring Tools:
- **AWS CloudWatch** for infrastructure metrics (CPU, memory, latencies).
- **Laravel Telescope** (debug) and **Sentry/NewRelic** for error tracking and performance monitoring.
- **Prometheus + Grafana** for custom dashboards (optional).

Maintenance Practices:
- **Automated backups** of RDS and Redis snapshots.
- **CI/CD Pipeline** (e.g., GitHub Actions) to run tests (PHPUnit, Jest), code analysis (PHPStan), and automated deployments.
- **Scheduled migrations and seeders** via `artisan migrate --force` in controlled windows.
- **Updater Module** handles atomic system upgrades, rollbacks, and backup before each release.

## 9. Conclusion and Overall Backend Summary

This backend is designed for high scalability, maintainability, and security:
- A **modular monolith** in Laravel keeps the codebase organized.
- **Node.js** handles real-time workflows in a decoupled service.
- **MySQL** and **Redis** deliver reliable data storage and fast caching/messaging.
- A **RESTful API** with versioning ensures future mobile apps can integrate seamlessly.
- AWS hosting and managed services provide robust availability and cost control.
- Comprehensive security, monitoring, and maintenance practices safeguard data and system health.

Together, these components form a solid foundation for a full-featured, enterprise-grade Logistics Delivery Management System that is easy to extend, customize, and operate.