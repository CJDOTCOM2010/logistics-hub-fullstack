# App Flow Document

## Onboarding and Sign-In/Sign-Up

When a new user arrives at the logistics platform, they first see the public landing page that introduces the service and offers options to log in or register. To sign up, the user clicks the Register button, enters a valid email address, chooses a secure password, confirms the password, and submits the form. An email is sent with a confirmation link that the user must click to verify their account. After verification, the user is directed to the login page, where they enter their email and password to access the system.

For password recovery, the user clicks the “Forgot Password” link on the login page, enters their registered email address, and receives a reset link by email. Clicking the link brings them to a secure reset page where they set a new password and confirm it. They are then redirected to the login page with a success message.

Super Admins use a dedicated URL to access their exclusive control panel. They sign in with a separate Super Admin login page and may also be required to complete two-factor authentication. They do not register through the public sign-up flow but receive credentials directly from the system owner or through a manual admin provisioning process.

## Main Dashboard or Home Page

Once authenticated, each user is taken to a role-specific dashboard that serves as the home page. The dashboard features a header bar with the application logo, user avatar, notifications icon, and a settings menu. A collapsible sidebar on the left lists the modules available to the user, such as Shipments, Tracking, Chat, Accounting, HR, or Super Admin Settings, depending on their role.

The central area of the dashboard displays widgets and summaries relevant to the user. For a Customer role, this includes upcoming deliveries, recent shipment statuses, and quick access to create a new booking. For a Driver role, the dashboard shows assigned tasks, route details, and live location controls. For an Admin or Super Admin, the dashboard presents system metrics, permission controls, and site customization panels. Users click items in the sidebar or top navigation to move to other parts of the application.

## Detailed Feature Flows and Page Transitions

When a user wants to create a new shipment, they navigate from the dashboard to the Shipments module. They click the “New Shipment” button, which opens a full-page form asking for sender and recipient details, package dimensions, and service options. After filling out the fields and reviewing the pricing estimate, the user clicks “Submit” and is shown a confirmation page with a tracking number. From here, they can go back to the shipments list or download a shipping label.

To schedule a pickup, the user opens any existing shipment from the list, clicks “Schedule Pickup,” selects date and time slots in a calendar widget, and confirms. The system then updates the shipment status and sends a notification to the assigned driver.

Within the Consolidation feature, the user selects multiple shipments from the list, groups them under a single consolidation batch, and clicks “Create Batch.” The system displays a batch detail page where the user can assign the batch to a locker location or hub for local storage.

Drivers access their Dashboard to see all daily tasks. When they start a route, they tap “Go Live,” which triggers the mobile or web app to send geolocation updates via WebSocket. On the Customer side, the Tracking module listens to these real-time events and renders the driver’s position on an interactive map. If the connection is lost, the map stops updating and shows a reconnecting indicator.

The chat feature is available from a persistent chat icon in the header. Clicking it slides open a chat panel where users can select existing conversations or start a new one by searching for another user or support agent. Messages appear in real time, and attachments like images or documents can be uploaded directly in the chat window.

In the Accounting module, users open Payments from the sidebar to view a list of recent transactions. Clicking on a transaction reveals a detail page with status, date, and line items. To schedule a payout, they click “Payout Schedule,” choose the recipient, amount, and date, and confirm. The system processes the request in the background and sends a notification when completed.

The HR module provides an Employee list view where admins can click “Add Employee.” The onboarding form collects personal information, role assignment, and contact details. In the same flow, the admin uploads KYC documents and captures a live facial ID. After submission, the system validates the documents and sends an approval or rejection notification.

Super Admins use a specialized module called Website Builder. They navigate to the Content section, pick a page to edit, and modify text blocks, images, or SEO metadata using a simple WYSIWYG editor. After saving changes, the public site immediately reflects the new content without redeployment.

The System Updater is accessed from the Super Admin sidebar. On the updater page, the admin clicks “Check for Updates,” at which point the system checks a remote server for new releases. If an update is available, the admin clicks “Apply Update,” triggering an automated backup sequence followed by the installation of the new package. A progress bar shows each step, and once it completes successfully, the system displays a confirmation message.

## Settings and Account Management

Users access their personal account settings from the header menu under “My Profile.” The profile page allows them to update name, email, phone number, and address. They can change their password by providing the current password and a new password twice for confirmation. On the Notifications tab, they toggle on or off email and in-app alerts for different event types.

Subscription or billing settings live under a separate “Billing” section for users on paid plans. Here, users view their current plan, usage statistics, and next billing date. To change plans, they click “Change Plan,” pick a new tier, enter payment details in a secure form, and confirm. After the update, the new plan takes effect immediately and the system returns them to the dashboard.

After managing any settings, users click “Save” or “Back to Dashboard” to return to their main view without losing context.

## Error States and Alternate Paths

If a user enters incorrect credentials at login, an inline error message appears above the form saying “Invalid email or password.” The user can retry or choose “Forgot Password” to recover their login. When filling any form, leaving required fields blank triggers red highlight borders and messages like “This field is required.” Entering an invalid phone number or email displays a validation message next to the field.

In the case of network disruptions, a persistent notification bar at the top informs the user that the connection has been lost. Actions such as creating shipments or scheduling pickups are temporarily disabled until connectivity returns. Real-time maps and chat windows show a spinning indicator and attempt to reconnect automatically.

If a user tries to access a page they do not have permission for, they are shown a 403 Forbidden page with a brief explanation and a button to return to their dashboard. During the update process, if an error occurs, the updater automatically rolls back to the previous state and displays an error page with details and a “Contact Support” link.

## Conclusion and Overall App Journey

A user begins by registering with their email, verifying their address, and logging in for the first time. They land on a customized dashboard based on their role, where they can quickly create shipments or manage tasks. Throughout the day, they schedule pickups, group shipments into consolidation batches, and track drivers in real time on interactive maps. They use the chat feature for instant communication and receive notifications for all important events.

Admins handle financial operations in the Accounting section, view and approve payouts, and onboard employees through the HR module with secure KYC checks. Super Admins go further by customizing the public site, managing roles and permissions, and applying system updates without touching any code. Error messages guide users back to normal flow whenever something goes wrong.

In typical usage, the user’s end goal—whether it is booking a delivery, tracking a package, running payroll, or updating site content—is accomplished through clear, connected pages and intuitive transitions that ensure the entire logistics management process is smooth and efficient every step of the way.