# Course Registration System

A comprehensive web-based course registration system built with PHP, MySQL, HTML, CSS, and JavaScript for educational institutions. Enhanced with advanced cookie functionality for persistent login sessions (Lab 9 implementation).

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

### Cookie Features (Lab 9)
- **Remember Me Functionality**: Checkbox option for persistent login sessions
- **Auto-Login**: Automatic authentication using stored cookies
- **Cookie Security**: HTTPOnly cookies with proper expiration management
- **Cookie Management**: Clean cookie clearing on logout
- **Cookie Demonstration**: Interactive page for testing and viewing cookies

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
2. The project is now organized in a structured directory layout:
   - `index.php` (root file that redirects to auth)
   - `src/auth/` (authentication files including login/logout)
   - `src/pages/` (main application pages)
   - `src/config/` (configuration files)
   - `src/examples/` (cookie demonstration files for Lab 9)
   - `assets/css/` (styling files)
   - `docs/` (documentation)

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
   - ✅ **Remember Me**: Check "Remember me for 30 days" for automatic login
3. **Register for Courses**: Navigate to "Register Course" to browse and enroll
4. **Manage Courses**: Use "My Courses" to view enrolled courses and submit drop requests

### Lecturer Access
1. **Login**: Select "Lecturer" radio button and use lecturer credentials
   - ✅ **Remember Me**: Check "Remember me for 30 days" for automatic login
2. **Review Requests**: Check dashboard for pending drop requests
3. **Make Decisions**: Approve or reject requests based on student information

### Cookie Features Testing
1. **Remember Me**: Login with "Remember me" checked, then logout and return
2. **Auto-Login**: Observe automatic login when returning to the site
3. **Cookie Demo**: Visit `/src/examples/cookie_demo.php` to view and manage cookies
4. **Cookie Clearing**: Logout to see cookies being properly cleared

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
├── index.php              # Root redirect to organized structure
├── src/                   # Main application source code
│   ├── auth/             # Authentication related files
│   │   ├── index.php     # Main login/signup page with remember me
│   │   └── logout.php    # Logout functionality with cookie clearing
│   ├── pages/            # Main application pages
│   │   ├── dashboard.php          # Student dashboard
│   │   ├── lecturer_dashboard.php # Lecturer interface
│   │   ├── register_course.php    # Course registration page
│   │   ├── my_courses.php         # Student course management
│   │   └── about.php              # About page
│   ├── config/           # Configuration files
│   │   └── config.php    # Database configuration
│   └── examples/         # Demo and example files
│       ├── cookie_demo.php # Cookie demonstration page (Lab 9)
│       ├── cookie1.php     # Basic cookie example
│       └── cookie2.php     # Cookie deletion example
├── assets/               # Frontend assets
│   └── css/              # Modular CSS structure
│       ├── base.css      # Global foundation styles
│       ├── components.css # Reusable UI components
│       ├── dashboard.css  # Dashboard layouts
│       ├── forms.css      # Form styling
│       ├── lecturer-dashboard.css # Lecturer-specific styles
│       └── utilities.css  # Utility classes
├── docs/                 # Documentation
│   └── Lab9_Report.pdf   # Lab report
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
   - Visit `/src/examples/cookie_demo.php` to view current cookie status
   - Ensure cookies are enabled in your browser

### Technical Support

This is a demonstration system for educational purposes. For issues:
1. Check XAMPP services are running
2. Verify database import was successful
3. Review browser console for JavaScript errors
4. Check PHP error logs in XAMPP

## System Requirements

- **Server**: Apache (included in XAMPP)
- **Database**: MySQL/MariaDB (included in XAMPP)
- **PHP**: Version 7.4 or higher
- **Browser**: Modern web browser with JavaScript enabled

## Lab 9 - PHP Cookies Implementation

This project includes a comprehensive implementation of PHP cookies for educational purposes:

### What Was Implemented
- ✅ **Remember Me checkbox** in login form
- ✅ **Automatic login** using stored cookies
- ✅ **Secure cookie handling** with HTTPOnly flags
- ✅ **Cookie expiration management** (30-day persistence)
- ✅ **Proper cookie clearing** on logout
- ✅ **Interactive cookie demonstration** page

### Key PHP Cookie Functions Used
- `setcookie()` - Creating cookies with security settings
- `$_COOKIE` - Reading cookie values
- Cookie clearing using past expiration times

### Testing the Cookie Features
1. Login with "Remember me for 30 days" checked
2. Logout and return to see automatic login
3. Visit `/cookie_demo.php` for interactive cookie management
4. Observe green notifications when auto-logged in

### Cookie Security Features
- HTTPOnly cookies prevent JavaScript access
- Proper expiration times (30 days for remember me)
- Clean cookie deletion on logout
- Secure cookie path and domain settings

## Security Notes

This is a basic demonstration system. For production use, consider:
- Implementing password hashing
- Adding input validation and sanitization
- Using CSRF protection
- Implementing proper session management
- Adding SSL/HTTPS support
- Using secure cookie flags (Secure, SameSite)

## License

This project is created for educational purposes. Feel free to use and modify as needed for learning. 