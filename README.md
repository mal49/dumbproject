# Course Registration System

A simple web-based course registration system built with PHP, MySQL, HTML, CSS, and JavaScript for educational institutions.

## Features

### For Students
- **User Registration**: Create accounts with complete profile information
- **Course Registration**: Browse and register for available courses
- **Course Management**: View registered courses and track enrollment status
- **Drop Requests**: Submit course drop requests with reasons to assigned lecturers
- **Request Tracking**: Monitor the status of drop requests

### For Lecturers
- **Request Management**: View all course drop requests assigned to them
- **Decision Making**: Approve or reject student drop requests
- **Student Information**: Access student details for informed decision making
- **Course Overview**: View all available courses in the system

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
2. Make sure all files are in the same directory:
   - `index.php` (main login/signup page)
   - `config.php` (database configuration)
   - `dashboard.php` (student dashboard)
   - `register_course.php` (course registration)
   - `my_courses.php` (student course management)
   - `lecturer_dashboard.php` (lecturer interface)
   - `about.php` (about page)
   - `logout.php` (logout functionality)
   - `styles.css` (styling)

### 3. Configuration
1. Open `config.php` and verify the database connection settings:
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
3. **Register for Courses**: Navigate to "Register Course" to browse and enroll
4. **Manage Courses**: Use "My Courses" to view enrolled courses and submit drop requests

### Lecturer Access
1. **Login**: Select "Lecturer" radio button and use lecturer credentials
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
├── index.php              # Main login/signup page
├── config.php             # Database configuration
├── dashboard.php          # Student dashboard
├── register_course.php    # Course registration page
├── my_courses.php         # Student course management
├── lecturer_dashboard.php # Lecturer interface
├── about.php             # About page
├── logout.php            # Logout functionality
├── styles.css            # Main stylesheet
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

## Security Notes

This is a basic demonstration system. For production use, consider:
- Implementing password hashing
- Adding input validation and sanitization
- Using CSRF protection
- Implementing proper session management
- Adding SSL/HTTPS support

## License

This project is created for educational purposes. Feel free to use and modify as needed for learning. 