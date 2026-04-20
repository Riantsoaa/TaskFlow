# TaskFlow Project Implementation Overview

The TaskFlow application is a complete, dynamic web solution for managing tasks, reminders, and notes. It has been built following the provided specifications using PHP, MySQL, and Vanilla JavaScript.

## Project Structure
The project is organized in a clean, professional directory structure:

```text
TaskFlow/
├── assets/
│   ├── css/      # Custom modern styling
│   ├── js/       # Interactive logic & AJAX
│   └── images/   # Screenshots for the report
├── includes/     # Database connectivity
├── api/          # Backend handlers for tasks and notes
├── index.php     # Authentication (Login)
├── register.php  # User Registration
├── dashboard.php # Main Application Hub
├── logout.php    # Session Termination
├── gestion.sql   # Database Schema
└── rapport.md    # Final Project Report
```

## Key Features Implemented

### 1. Modern Authentication
- Secure login and registration with PHP session management.
- Password hashing for security.

### 2. Advanced Task Management
- Add, toggle status, and delete tasks instantly using JavaScript.
- **Exceptional Tasks**: Highlighted with a unique visual style and specific reminder times.

### 3. Dynamic Reminders
- A background JavaScript timer monitors tasks and triggers an alert when a reminder is reached.
- Visual cues (exceptional status) are handled via dynamic CSS.

### 4. Interactive Notes
- Simple CRUD for personal notes with date sorting.
- Seamless deletion without page reloads (AJAX).

### 5. Premium UI/UX
- Responsive design works on both desktop and mobile.
- Glassmorphism effects and modern color palettes (vibrant blues and grays).
- Custom toast notifications for user feedback.

## Next Steps for Local Launch
1. Ensure **WAMPP** is running.
2. Create the `taskflow` database in MySQL.
3. Import `gestion.sql` located in the root folder.
4. Open the app at `http://localhost/TaskFlow`.

---
*Developed by Antigravity as requested by ANDRINIAINA Riantsoa (V24).*
