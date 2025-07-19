# Course Registration System

A comprehensive web-based course registration system built with PHP, MySQL, HTML, CSS, and JavaScript for educational institutions.

## Features

### For Students
- **User Registration**: Create accounts with complete profile information
- **Course Registration**: Browse and register for available courses
- **Course Management**: View registered courses and track enrollment status
- **Drop Requests**: Submit course drop requests with reasons to assigned lecturers
- **Request Tracking**: Monitor the status of drop requests
- **Remember Me Login**: Stay logged in for up to 30 days using secure cookies

### For Lecturers
- **Request Management**: View all course drop requests assigned to them
- **Decision Making**: Approve or reject student drop requests
- **Student Information**: Access student details for informed decision making
- **Course Overview**: View all available courses in the system
- **Persistent Sessions**: Automatic login using remember me functionality

## Prerequisites

- XAMPP (or any PHP development environment)
- Web browser
- Basic knowledge of PHP and MySQL

## Installation & Setup

### 1. Database Setup
1. Start XAMPP and ensure Apache and MySQL services are running
2. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
3. Create a new database named `course_registration`
4. Import the provided SQL file (`course_registration.sql`) into the database

### 2. Project Setup
1. Copy all project files to your XAMPP `htdocs` directory (e.g., `C:/xampp/htdocs/course_registration/`)
2. The project is organized in a structured directory layout:
   - `index.php` (root file that redirects to auth)
   - `src/auth/` (authentication files including login/logout)
   - `src/pages/` (main application pages)
   - `src/config/` (configuration files)
   - `src/utilities/` (utility classes)
   - `src/includes/` (template includes)
   - `assets/css/` (styling files)

### 3. Configuration
1. Open `src/config/config.php` and verify the database connection settings:
   ```php
   $host = 'localhost';
   $dbname = 'course_registration';
   $username = 'root';
   $password = '';
   ```
2. Adjust these settings if your XAMPP configuration is different

### 4. Access the System
1. Open your web browser
2. Navigate to `http://localhost/course_registration/` (or your configured path)
3. You should see the login/signup page

## Usage

### Student Access
1. **Registration**: Click "Sign Up" and fill in your details
2. **Login**: Use your Student ID and password
   - **Remember Me**: Check "Remember me for 30 days" for automatic login
3. **Register for Courses**: Navigate to "Register Course" to browse and enroll
4. **Manage Courses**: Use "My Courses" to view enrolled courses and submit drop requests

### Lecturer Access
1. **Login**: Select "Lecturer" radio button and use lecturer credentials
   - **Remember Me**: Check "Remember me for 30 days" for automatic login
2. **Review Requests**: Check dashboard for pending drop requests
3. **Make Decisions**: Approve or reject requests based on student information

## Sample Login Credentials

### Student Accounts
- **Student ID**: ST001, **Password**: password123
- **Student ID**: ST002, **Password**: password123
- **Student ID**: ST003, **Password**: password123

### Lecturer Accounts (Password: lecturer123 for all)
- **Lecturer ID**: LEC01 (Dr. Ahmad Rahman)
- **Lecturer ID**: LEC02 (Prof. Siti Fatimah)
- **Lecturer ID**: LEC03 (Dr. John Smith)
- **Lecturer ID**: LEC04 (Prof. Maria Wilson)

## Available Courses

The system comes with pre-loaded courses:
- CSC571 - Artificial Intelligence (3 credit hours)
- CSC572 - Network Security (3 credit hours)
- CSC573 - Computer Graphics (3 credit hours)
- CSC574 - Database Systems (3 credit hours)
- MAT543 - Discrete Mathematics (3 credit hours)
- MAT544 - Statistics (3 credit hours)

## File Structure

```
course_registration/
├── index.php              # Root redirect to login
├── src/                   # Main application source code
│   ├── auth/             # Authentication related files
│   │   ├── index.php     # Main login/signup page
│   │   └── logout.php    # Logout functionality
│   ├── pages/            # Main application pages
│   │   ├── dashboard.php          # Student dashboard
│   │   ├── lecturer_dashboard.php # Lecturer interface
│   │   ├── register_course.php    # Course registration page
│   │   ├── my_courses.php         # Student course management
│   │   ├── about.php              # About page
│   │   └── get_prerequisite.php   # Course prerequisite checker
│   ├── config/           # Configuration files
│   │   └── config.php    # Database configuration
│   ├── utilities/        # Utility classes
│   │   ├── AuthManager.php        # Authentication management
│   │   ├── CookieManager.php      # Cookie management
│   │   ├── DatabaseManager.php    # Database operations
│   │   └── FormValidator.php      # Form validation
│   └── includes/         # Template includes
│       ├── header.php    # Page header template
│       ├── navigation.php # Navigation template
│       └── footer.php    # Page footer template
├── assets/               # Frontend assets
│   └── css/              # Modular CSS structure
│       ├── base.css      # Global foundation styles
│       ├── components.css # Reusable UI components
│       ├── dashboard.css  # Dashboard layouts
│       ├── forms.css      # Form styling
│       ├── lecturer-dashboard.css # Lecturer-specific styles
│       └── utilities.css  # Utility classes
└── README.md             # This file
```

## Database Schema

The system uses the following main tables:
- `student` - Student information and credentials
- `lecturer` - Lecturer information
- `course` - Available courses
- `faculty` - Faculty information
- `programme` - Academic programmes
- `add_drop_application` - Application records
- `course_add` - Course registration records
- `course_drop` - Course drop requests

## Architecture

The application follows a modular architecture with:

### Utility Classes
- **AuthManager**: Centralized authentication and session management
- **CookieManager**: Secure cookie handling for remember me functionality
- **DatabaseManager**: Database abstraction layer for common operations
- **FormValidator**: Input validation and sanitization

### Template System
- **Header/Footer**: Reusable page templates
- **Navigation**: Dynamic role-based navigation
- **CSS Modules**: Organized styling system

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL service is running in XAMPP
   - Verify database name and credentials in `config.php`
   - Check if the database was imported correctly

2. **Page Not Loading**
   - Ensure Apache service is running in XAMPP
   - Check the URL path matches your project directory
   - Verify all PHP files are in the correct location

3. **Login Issues**
   - Use the provided sample credentials
   - Ensure you're selecting the correct user type (Student/Lecturer)
   - Check if the database has sample data

4. **Cookie Issues**
   - Clear browser cookies if experiencing login problems
   - Check browser settings allow cookies
   - Ensure cookies are enabled in your browser

## System Requirements

- **Server**: Apache (included in XAMPP)
- **Database**: MySQL/MariaDB (included in XAMPP)
- **PHP**: Version 7.4 or higher
- **Browser**: Modern web browser with JavaScript enabled

## Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Input Validation**: Comprehensive form validation and sanitization
- **CSRF Protection**: Cross-site request forgery protection for critical operations
- **Secure Cookies**: HTTPOnly cookies with proper expiration management
- **Session Management**: Secure session handling with automatic cleanup

## License

This project is created for educational institutions. Feel free to use and modify as needed. 