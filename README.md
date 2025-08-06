# School Results Management System - PHP Version

A comprehensive school results management system built with vanilla PHP, Bootstrap, and SQLite database. This system is designed for Tanzanian secondary schools supporting both Ordinary Level (Forms 1-4) and Advanced Level (Forms 5-6) education.

## Features

### User Management
- **Super Admin**: Full system access, user management, system configuration
- **Principal**: View all data, generate reports, analytics access
- **Teacher**: Enter/edit results for assigned subjects only
- **Class Teacher**: All teacher permissions plus class-specific management

### Academic Structure
- Support for Forms 1-6 (O-Level and A-Level)
- 20 O-Level subjects and 9 A-Level subjects as per Tanzanian curriculum
- 2 terms per academic year system
- Automatic grade calculation and division assignment

### Results Management
- Continuous Assessment (CA) and Final Exam marks entry
- Automatic average calculation and grade assignment
- Real-time division calculation
- O-Level: Best 7 subjects for division calculation
- A-Level: All 4 subjects for division calculation

### Grading System
#### O-Level Grading:
- Grade A: 75-100% → 1 point
- Grade B: 65-74% → 2 points  
- Grade C: 45-64% → 3 points
- Grade D: 30-44% → 4 points
- Grade F: 0-29% → 5 points

#### A-Level Grading:
- Grade A: 80-100% → 1 point
- Grade B: 70-79% → 2 points
- Grade C: 60-69% → 3 points
- Grade D: 50-59% → 4 points
- Grade E: 40-49% → 5 points
- Grade F: Below 35% → 6 points

### Division Calculation
#### O-Level Divisions:
- Division I: 7-17 points
- Division II: 18-21 points
- Division III: 22-25 points
- Division IV: 26-33 points
- Division 0: 34+ points

#### A-Level Divisions:
- Division I: 3-9 points
- Division II: 10-12 points
- Division III: 13-17 points
- Division IV: 18-19 points
- Division 0: 20+ points

## Installation

### Requirements
- PHP 7.4 or higher
- SQLite3 extension enabled
- Web server (Apache/Nginx)

### Setup Instructions

1. **Download and Extract**
   \`\`\`bash
   # Extract the php-version folder to your web server directory
   # For XAMPP: htdocs/php-version
   # For WAMP: www/php-version
   \`\`\`

2. **Set Permissions**
   \`\`\`bash
   # Make sure the database directory is writable
   chmod 755 database/
   chmod 666 database/ (if database file exists)
   \`\`\`

3. **Run Setup**
   - Navigate to `http://localhost/php-version/setup.php`
   - Click "Setup Database" to create tables and sample data
   - Wait for confirmation message

4. **Login**
   - Go to `http://localhost/php-version/login.php`
   - Use demo credentials provided below

## Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@school.com | password |
| Principal | principal@school.com | password |
| Teacher | john@school.com | password |
| Class Teacher | mary@school.com | password |

## File Structure

\`\`\`
php-version/
├── config/
│   ├── config.php          # Application configuration
│   └── database.php        # Database connection class
├── classes/
│   ├── Auth.php           # Authentication class
│   ├── User.php           # User management class
│   ├── Student.php        # Student management class
│   ├── Subject.php        # Subject management class
│   ├── Result.php         # Results management class
│   └── DivisionCalculator.php # Division calculation logic
├── database/
│   ├── schema.sql         # Database schema
│   ├── seed_data.sql      # Sample data
│   └── school_results.db  # SQLite database (created after setup)
├── includes/
│   ├── functions.php      # Helper functions
│   ├── header.php         # Common header
│   ├── sidebar.php        # Navigation sidebar
│   └── footer.php         # Common footer
├── results/
│   ├── index.php          # Results overview
│   ├── enter.php          # Results entry form
│   └── view.php           # View results
├── students/
│   └── index.php          # Student management
├── teachers/
│   └── index.php          # Teacher management
├── subjects/
│   └── index.php          # Subject management
├── reports/
│   └── index.php          # Reports section
├── login.php              # Login page
├── logout.php             # Logout handler
├── dashboard.php          # Main dashboard
├── setup.php              # Database setup
└── README.md              # This file
\`\`\`

## Key Features

### Dashboard
- Role-specific dashboards with relevant information
- Statistics and progress tracking
- Quick action buttons
- Recent activity logs

### Results Entry
- Intuitive form interface for entering CA and Exam marks
- Real-time grade calculation
- Progress tracking per subject
- Bulk entry capabilities

### Division Calculation
- Automatic calculation based on Tanzanian education system
- Best 7 subjects for O-Level
- All subjects for A-Level
- Historical tracking

### Security Features
- Password hashing with PHP's password_hash()
- CSRF token protection
- Role-based access control
- Session management
- SQL injection prevention with prepared statements

### Responsive Design
- Bootstrap 5 framework
- Mobile-friendly interface
- Print-friendly reports
- Modern UI with icons

## Usage

### For Super Admins
1. Manage users (teachers, students)
2. Set up subjects and classes
3. Configure academic years and terms
4. View all system data and reports

### For Principals
1. View school performance analytics
2. Generate comprehensive reports
3. Monitor division statistics
4. Track top performers

### For Teachers
1. Enter results for assigned subjects
2. View student performance in their subjects
3. Generate subject-specific reports
4. Track results entry progress

### For Class Teachers
1. All teacher functions
2. Manage assigned class students
3. View class performance overview
4. Generate class reports

## Customization

### Adding New Subjects
1. Login as Super Admin
2. Go to Subjects section
3. Add new subject with appropriate level (ordinary/advanced)

### Modifying Grading System
Edit the `calculateGrade()` function in `includes/functions.php`

### Changing Academic Structure
Modify the database schema and update relevant classes

## Troubleshooting

### Database Issues
- Ensure SQLite extension is enabled in PHP
- Check file permissions on database directory
- Re-run setup.php if database is corrupted

### Login Problems
- Verify demo credentials
- Check if setup was completed successfully
- Clear browser cache and cookies

### Permission Errors
- Ensure proper file permissions
- Check user roles in database
- Verify session configuration

## Support

For technical support or questions:
1. Check the troubleshooting section
2. Review the code comments for implementation details
3. Verify database integrity using SQLite browser tools

## License

This project is open source and available under the MIT License.

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Note**: This system is designed specifically for Tanzanian secondary schools but can be adapted for other educational systems by modifying the grading scales and academic structure.
