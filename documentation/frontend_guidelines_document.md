# Frontend Guideline Document

This document outlines the frontend setup for the Logistics Management Platform. It explains how we build, style, organize, and test the user interface so that everyone—from designers to new developers—can understand and follow the same standards.

## 1. Frontend Architecture

### Overview
We use **Inertia.js** together with **Vue 3** to create a smooth, single-page experience without leaving the Laravel ecosystem. Behind the scenes, **Vite** bundles our assets, making development fast and production builds lean.

### Key Parts
- **Inertia.js**: Bridges Laravel routes and Vue pages, so each page feels like a traditional server-rendered view but behaves like an SPA (Single-Page Application).
- **Vue 3**: Our JavaScript framework for building reusable components and handling interactivity.
- **Vite**: The development server and build tool. It offers lightning-fast hot-module replacement and smart bundling for production.
- **Tailwind CSS**: A utility-first CSS toolkit for quick, consistent styling without writing large custom stylesheets.

### Why This Architecture?
- **Scalability**: Splitting UI into small components keeps code organized as the app grows.
- **Maintainability**: Inertia lets backend and frontend teams work side by side—controllers and pages follow a clear convention.
- **Performance**: Vite’s modern bundling and Tailwind’s PurgeCSS ensure we ship only what’s needed to the browser.

## 2. Design Principles

We follow three main principles when designing user interfaces:

1. **Usability**: Interfaces must be intuitive. Buttons, forms, and navigation are clearly labeled and placed where users expect them.
2. **Accessibility**: We ensure color contrast, keyboard navigation, and proper ARIA attributes so everyone—including people with disabilities—can use the platform.
3. **Responsiveness**: The layout adapts smoothly to desktops, tablets, and phones. We use mobile-first breakpoints in Tailwind to style components for small screens first, then enhance for larger devices.

### How We Apply Them
- **Clear Labels**: Every form field has a visible label. Error messages appear immediately when input is invalid.
- **Skip Links & Focus States**: We include a skip-to-main-content link and distinct focus outlines on interactive elements.
- **Flexible Grids**: Page layouts use Tailwind’s grid or flex utilities to rearrange content based on screen width.

## 3. Styling and Theming

### Styling Approach
- **Utility-First with Tailwind CSS**: We build layouts and components by composing pre-defined utility classes. This reduces custom CSS and keeps styles consistent.
- **Atomic & BEM-like Naming**: When custom classes are needed, we follow a simplified BEM pattern (`.card`, `.card__header`, `.card--highlight`) to describe blocks, elements, and modifiers.

### Theming
All global colors and font sizes live in `tailwind.config.js`. We can switch themes (for example, light/dark) by toggling a CSS class on the `<html>` element.

### Visual Style
- Overall feel: **Modern Flat with Subtle Glassmorphism** on cards and modals (slight translucency, soft shadows).
- **Color Palette**:
  • Primary: #3B82F6 (blue)  
  • Secondary: #10B981 (green)  
  • Accent: #F59E0B (amber)  
  • Neutral (dark): #1F2937  
  • Neutral (light): #F3F4F6  
  • Success: #16A34A  
  • Warning: #D97706  
  • Danger: #DC2626
- **Fonts**: Use **Inter** for body text and **Poppins** for headings. Both are available via Google Fonts.

## 4. Component Structure

### Folder Layout
```
resources/
  js/
    Components/      # Reusable UI pieces (buttons, inputs, cards)
      atoms/          # Smallest building blocks (Icon, Badge)
      molecules/      # Grouped atoms (FormField, NavItem)
      organisms/      # Larger sections (Navbar, Sidebar)
    Pages/           # Inertia page components (Dashboard.vue, Shipments.vue)
    Layouts/         # Common page wrappers (AppLayout.vue)
    store/           # Pinia state stores
```

### Why Component-Based?
- **Reusability**: Write once, use everywhere—buttons and form fields behave consistently.
- **Isolation**: Each component manages its own logic and styles, reducing unexpected side effects.
- **Readability**: Smaller files focused on a single piece of UI are easier to understand and test.

## 5. State Management

### Pinia Store
We use **Pinia**, Vue’s official state library, to manage shared data (like current user info or cart contents). Pinia stores live in `resources/js/store/`.

- **Global Stores**: For data accessed by many components (e.g., user profile, notifications).
- **Local Stores/Props**: Page-specific data is fetched in page components via Inertia and passed down as props.

This approach keeps state predictable and easy to debug.

## 6. Routing and Navigation

- **Laravel Routes**: Define URLs in `routes/web.php` and point them to controller actions.
- **Inertia Links**: Use `<inertia-link>` in Vue templates to navigate. Inertia intercepts clicks and makes XHR requests, replacing only the page content.
- **Navigation Structure**:
  • Main menu (Dashboard, Shipments, Drivers, Reports) lives in a Sidebar component.  
  • Breadcrumbs appear at the top of each page to show location within the app.  

Users move between pages seamlessly, with no full-page reloads, thanks to Inertia.

## 7. Performance Optimization

- **Lazy Loading**: Large components (like the map view) are loaded on demand with dynamic imports.
- **Code Splitting**: Vite automatically splits code into smaller chunks so users only download what they need.
- **Asset Optimization**: Images are compressed, and unused CSS classes are removed in production builds (`purge` in Tailwind).
- **Caching**: Static assets get hashed filenames, allowing browsers to cache them aggressively.

These steps reduce initial load times and keep interactions snappy.

## 8. Testing and Quality Assurance

- **Unit Tests**: Use **Vue Test Utils** with **Jest** to test individual components in `__tests__/components`.
- **Integration Tests**: Combine components and stores in Jest to ensure they work together.
- **End-to-End (E2E) Tests**: Use **Cypress** for critical user flows (login, booking a shipment, tracking). Test specs live in `cypress/integration/`.
- **Linting & Formatting**:
  • **ESLint**: Enforce code style and catch common errors.  
  • **Prettier**: Auto-format code for consistency.  
  • **Stylelint**: Lint custom CSS if used.

Running `npm run lint`, `npm run test:unit`, and `npm run test:e2e` should pass in CI before merging any pull request.

## 9. Conclusion and Overall Frontend Summary

Our frontend follows a clear, component-driven approach built on Inertia.js, Vue 3, and Tailwind CSS. It balances modern design, performance, and maintainability by:

- Separating UI into reusable atoms, molecules, and organisms.
- Managing shared data with Pinia and routing seamlessly with Inertia.
- Ensuring consistent styling through a defined color palette, fonts, and a utility-first CSS method.
- Keeping quality high with unit, integration, and E2E tests.

By following these guidelines, everyone on the team can build new features or update existing ones in a predictable, efficient, and user-friendly way.