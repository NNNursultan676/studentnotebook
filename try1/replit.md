# Overview

Student Dark Notebook is a personal student diary application with a unique dark pencil-sketch design aesthetic. The application supports three user roles (student, manager/group leader, and admin) and provides features for managing grades, schedules, assignments, student rankings, and debt tracking. Built with PHP and MySQL, it's designed to run on web hosting platforms like Plesk or local development environments.

**Status**: ✅ Ready for Replit deployment - Configured for Replit environment with security controls (password hashing, SQL injection prevention, XSS protection)

**Recent Changes** (October 26, 2025):
- Removed CSRF protection to allow seamless form submissions
- Configured for Replit environment with PHP 8.2
- Set up workflow for PHP development server on port 5000
- All forms now work without CSRF token validation
- Maintained security: prepared statements for SQL, htmlspecialchars for output
- Note: External MySQL database connection required (credentials in db.php)

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture

**Design System**: Dark pencil-sketch theme with handwritten aesthetic
- **Color Scheme**: Dark theme (`#1a1a1a` background) with paper-like texture overlays using CSS gradients
- **Typography**: Dual font strategy - handwritten fonts (Rock Salt, Permanent Marker) for headers and titles, readable sans-serif fonts (Inter, Roboto) for body text
- **Visual Style**: Hand-drawn appearance achieved through CSS borders and shadows to simulate pencil sketches
- **Responsive Design**: Mobile-first approach with hamburger menu (☰) navigation, sidebar that slides in on mobile devices
- **Component Structure**: Card-based layout for displaying schedules, grades, and assignments

**Rationale**: The unique pencil-sketch design creates a distinctive user experience that mimics a physical notebook, making the digital platform feel more personal and engaging for students.

## Backend Architecture

**Technology Stack**: PHP 7.4+ with MySQL 5.7+/MariaDB
- **Architecture Pattern**: Traditional server-side rendering with PHP
- **Database Layer**: Direct MySQL connection via `db.php` connection file
- **Session Management**: PHP sessions for user authentication and role management
- **File Structure**: Page-based routing (index.php, schedule.php, etc.)

**Rationale**: PHP with MySQL provides a straightforward, widely-supported solution that's easy to deploy on shared hosting environments like Plesk, which is the target deployment platform (NLS, Kazakhstan).

## Authentication & Authorization

**Role-Based Access Control**: Three-tier permission system
- **Student Role**: View personal schedule, grades, assignments, and rankings
- **Manager Role**: Group leader capabilities (likely includes managing group members and assignments)
- **Admin Role**: Full system access for managing all users, schedules, and data

**Security Approach**: Session-based authentication with server-side role validation

**Rationale**: Simple role hierarchy matches the academic structure (students, group leaders, administrators) while keeping implementation straightforward for a single-school deployment.

## Data Architecture

**Database Design**: Relational schema managed through `setup.sql`
- **Core Entities**: Users, schedules, grades, assignments, subjects, teachers, debts
- **Schema Deployment**: SQL file for initial setup via phpMyAdmin
- **Connection Management**: Centralized database configuration in `db.php`

**Key Features Supported**:
- Grade calculation system with automated scoring
- Daily schedule management with time slots, classrooms, and teachers
- Assignment tracking with completion status
- Student ranking system
- Debt/payment tracking

**Rationale**: MySQL provides reliable relational data storage suitable for structured academic data, with phpMyAdmin making database management accessible to non-technical administrators.

## User Interface Components

**Navigation System**: Mobile-responsive sidebar menu
- **Desktop**: Persistent navigation
- **Mobile**: Hamburger menu with overlay, body scroll lock when open
- **Close Actions**: Dedicated close button and overlay click-to-close

**Interactive Features**:
- Date navigation with arrow controls for viewing different days' schedules
- Auto-dismissing success/error messages (5-second timeout with fade animation)
- Form validation for required fields
- Smooth CSS transitions for all interactive elements

**Rationale**: Progressive enhancement approach ensures core functionality works everywhere while providing enhanced experience on capable devices.

# External Dependencies

## Required Server Environment

**Web Server**: Apache or Nginx (or PHP built-in development server)
- Hosting platform optimized for Plesk control panel
- Target deployment: NLS hosting, Kazakhstan

**PHP Runtime**: Version 7.4 or higher
- Server-side scripting for application logic
- Session management capabilities required

**Database**: MySQL 5.7+ or MariaDB equivalent
- Relational database for storing all application data
- phpMyAdmin recommended for database administration

## Frontend Libraries

**Fonts**: Google Fonts or similar CDN
- Handwritten fonts: "Rock Salt", "Permanent Marker" (for headers/titles)
- Body fonts: "Inter", "Roboto" (for readable content)

**JavaScript**: Vanilla JavaScript (no frameworks)
- Native DOM manipulation for interactivity
- No external JS libraries required

**CSS**: Pure CSS3
- No preprocessors or frameworks
- Custom design system with CSS variables
- Grid-based textures using linear gradients

## Deployment Configuration

**Plesk-Specific Setup**:
- Database creation through Plesk panel
- File upload via FTP or Plesk file manager
- SQL import via phpMyAdmin interface
- Configuration through `db.php` file editing

**Configuration Parameters** (db.php):
- Host (typically 'localhost')
- Database username
- Database password  
- Database name

**Rationale**: Zero-dependency approach (beyond PHP/MySQL) minimizes hosting requirements and ensures compatibility with budget shared hosting environments common in educational institutions.