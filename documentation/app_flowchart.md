flowchart TD
    Start[Start] --> Landing[Landing Page]
    Landing --> Login[Login Page]
    Login --> Auth{Authenticated}
    Auth -- Yes --> Dashboard[Dashboard]
    Auth -- No --> Login
    Dashboard -- Create Shipment --> Booking[Shipment Booking]
    Booking --> Consolidation[Shipment Consolidation]
    Consolidation --> Review[Review Shipment]
    Booking -- Single Booking --> Review
    Review --> Submit[Submit Shipment]
    Submit --> Confirmation[Booking Confirmation]
    Dashboard -- Track Shipment --> Tracking[Shipment Tracking]
    Tracking --> Map[Map View]
    Map --> RealTime[Real Time Updates]
    RealTime --> Tracking
    Dashboard -- Chat --> Chat[Internal Chat]
    Dashboard -- Notifications --> Notifications[Notifications]
    Dashboard -- Super Admin --> AdminPanel[Super Admin Panel]
    AdminPanel --> Settings[Global Settings]
    AdminPanel --> Roles[Role Management]
    AdminPanel --> Modules[Module Permissions]
    AdminPanel --> CMS[Landing Page CMS]
    Dashboard -- Accounting --> Accounting[Accounting Module]
    Dashboard -- HR --> HR[HR Module]
    Dashboard -- API --> API[API Management]
    Dashboard -- Updater --> Updater[System Updater]