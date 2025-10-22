# Project Requirements Document (PRD)

## 1. Project Overview

This project aims to build a fully stand-alone Logistics Delivery Management System that gives a single Super Admin full control over every aspect of the platform—without touching code. From branding and landing-page content to granular, role-based permissions and module activation, the Super Admin can configure the entire system via a secure, dedicated dashboard. Behind the scenes, drivers, customers, accountants, HR staff, and other roles each have their own tailored dashboards and workflows, ensuring that every user only sees what they need.

At its core, the platform solves the fragmentation problem in logistics operations by unifying booking, tracking, communication, finance, and HR into one centralized system. Real-time driver geolocation, push notifications, and an internal chat keep everyone in sync. A robust API lets future mobile apps plug directly into the backend. Success will be measured by rapid setup by the Super Admin, seamless booking and delivery tracking by users, secure handling of sensitive data, and clear auditability of every action.

## 2. In-Scope vs. Out-of-Scope

### In-Scope (Version 1)
- Super Admin dashboard with:
  - Branding management (logo, favicon, theme colors)
  - CMS for landing pages and SEO settings
  - Role & permission management (using spatie/laravel-permission)
  - Module activation/deactivation
- User authentication & registration for all roles
- Role-based dashboards:
  - Customer portal for booking and tracking
  - Driver console for assignments and live updates
  - Basic Admin/Manager overview dashboards
- Shipment lifecycle management:
  - Single & batch booking
  - Pickup scheduling
  - Status logs & manual updates
- Real-time driver tracking via a Node.js WebSocket service and Redis events
- Instant notifications (browser popups & sounds)
- Internal chat between users, drivers, and support
- KYC module for document upload & facial verification
- Public website builder inside Super Admin portal
- Versioned RESTful API for mobile app integration
- Basic System Updater with automatic backups & rollback

### Out-of-Scope (Later Phases)
- Native iOS/Android mobile apps (API only in v1)
- Full accounting & payroll automation
- Advanced HR features (performance reviews, benefits management)
- Multi-language or localization support
- Offline or PWA capabilities
- Third-party logistics integrations (e.g., 3PL providers)
- Machine-learning route optimization

## 3. User Flow

A new user visits the public site, clicks “Sign Up,” and selects their role (Customer, Driver, Accountant, etc.). After email verification, they set up their profile and log in to a personalized dashboard. Customers see buttons to book a new shipment or view existing orders; drivers see an active assignment list with map widgets; accountants see transaction logs. When a customer creates a shipment, they fill in pickup and delivery details, choose additional services, and confirm. The system logs the booking and notifies a driver in real time.

Once a shipment is assigned, the driver’s app (or console) displays the route on an interactive map. As the driver updates status or moves along the route, the Node.js service broadcasts location updates and status changes to the customer’s tracking page. Both parties can exchange messages in the built-in chat. Meanwhile, the Super Admin can monitor all shipments, tweak system settings, and audit logs—all without writing code.

## 4. Core Features
- Authentication & Registration: Email/password, roles, password reset, 2FA for high-privilege accounts.
- Super Admin CMS: WYSIWYG editor for landing pages, SEO metadata, asset uploads.
- Role & Permission Engine: Create/assign roles, toggle module-level permissions.
- Dashboard Module: Custom widgets per role (KPIs, charts, quick actions).
- Shipment Management: CRUD for shipments, batch bookings, consolidation.
- Scheduling & Routing: Pickup/drop-off scheduling, manual driver assignment.
- Real-Time Geolocation: WebSocket service (Node.js + Socket.io), map rendering.
- Notifications: Browser popups & sounds via WebSocket channels.
- Internal Chat: One-to-one and group messaging among platform users.
- KYC Verification: File upload, document status tracking, facial ID check.
- Public Site Builder: Page templates, drag-and-drop sections, SEO forms.
- RESTful API: Versioned endpoints with token auth, rate limiting.
- System Updater: Secure package download, signature verification, auto-rollback.

## 5. Tech Stack & Tools
- Backend Framework: Laravel (PHP) for core application and API.
- Real-Time Service: Node.js with Socket.io, subscribing to Redis channels.
- Database: MySQL managed by Laravel Eloquent ORM.
- Frontend Rendering: Laravel Blade + Livewire/Alpine.js or Inertia.js with Vue/React.
- Styling: Tailwind CSS, reusable Blade or JS components.
- Mapping APIs: Google Maps or Mapbox for map displays.
- Queues & Caching: Redis for queues, session storage, and Pub/Sub.
- Authentication: Laravel Sanctum or Passport for API tokens.
- Testing: PHPUnit (backend), Jest/Vitest (frontend), Cypress or Dusk (E2E).
- CI/CD: GitHub Actions for linting (PHPStan, ESLint), tests, and deployments.

## 6. Non-Functional Requirements
- Performance: Page load & API 95th percentile under 200 ms; WebSocket latency under 100 ms.
- Security: OWASP Top 10 compliance; 2FA for Super Admin; encrypted data-at-rest for KYC docs; rate limiting on APIs.
- Availability: 99.9% uptime; zero-downtime deployments via blue/green or rolling updates.
- Scalability: Horizontally scalable Node.js service; use of queues for heavy tasks.
- Usability: Intuitive dashboards; responsive design for desktop and tablets.
- Auditability: Immutable logs for all critical actions (user changes, updater events).

## 7. Constraints & Assumptions
- Assumes access to Google Maps or Mapbox API keys and budget.
- Relies on Redis for Pub/Sub; must be highly available.
- Node.js service is a separate deployable unit.
- Super Admin user count is small; other roles may scale to thousands.
- Future mobile apps will consume the same API—design API versioning accordingly.

## 8. Known Issues & Potential Pitfalls
- API Rate Limits: Ensure clear error messages and retry logic if limits are hit.
- Real-Time Scaling: WebSockets can become a bottleneck; consider horizontal clustering or a managed socket service.
- Schema Migrations: Large tables (shipments, logs) may need chunked migrations in production.
- Geolocation Accuracy: GPS drift may confuse users; implement periodic smoothing or manual status updates.
- Updater Safety: Malformed packages risk downtime—test updater heavily in staging.
- Permission Complexity: A very granular RBAC can be hard to maintain; provide default templates for common roles.
