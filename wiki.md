# Project Summary
The project is an online education platform developed in PHP and MySQL, designed for distance learning (EAD). It features a multi-user system allowing for administrators, instructors, and students to interact within a structured environment. This platform facilitates course creation, enrollment, and management, providing a responsive interface and robust security measures.

# Project Module Description
The system consists of various functional modules, including:
- **User Management**: Handles user registration, login, and role assignments (admin, instructor, student).
- **Course Management**: Allows instructors to create, edit, and manage courses and lessons.
- **Enrollment System**: Manages student enrollments in courses and tracks progress.
- **Dashboard**: Provides personalized dashboards for different user roles with relevant statistics and actions.

# Directory Tree
```
/
├── config/
│   └── database.php          # Database configuration
├── classes/
│   ├── User.php             # User management class
│   ├── Course.php           # Course management class
│   ├── Enrollment.php       # Enrollment management class
│   └── Lesson.php           # Lesson management class
├── includes/
│   ├── auth.php             # Authentication functions
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── index.php                # Home page
├── login.php                # Login page
├── register.php             # Registration page
├── dashboard.php            # Main dashboard
├── my_courses.php           # User's courses
├── available_courses.php    # Available courses for enrollment
├── view_course.php          # View course details
├── create_course.php        # Create a new course
├── course_lessons.php       # Manage lessons for a course
├── create_lesson.php        # Create a new lesson
├── view_lesson.php          # View lesson details
├── manage_users.php         # User management (admin)
├── profile.php              # User profile
├── access_denied.php        # Access denied page
└── logout.php               # Logout action
```

# File Description Inventory
- `database.php`: Contains database connection settings.
- `User.php`: Manages user-related operations.
- `Course.php`: Manages course-related operations.
- `Enrollment.php`: Manages course enrollment operations.
- `Lesson.php`: Manages lesson-related operations.
- `auth.php`: Provides authentication functions and session management.
- `header.php` & `footer.php`: Common HTML structures for all pages.
- Various PHP files for handling user interactions, course management, and user profiles.

# Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Architecture**: MVC with PHP classes

# Usage
1. **Database Setup**:
   Execute the SQL file to create the database structure.
   
2. **Configuration**:
   Edit `config/database.php` with your MySQL credentials.

3. **Access the Application**:
   Use the provided user credentials to log in and explore the features.

4. **Explore Features**:
   Navigate through the dashboard, manage courses, and view lessons based on your role.
