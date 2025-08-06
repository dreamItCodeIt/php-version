# School Results Management System

A comprehensive web-based application for managing student results in Tanzanian government secondary schools, built with PHP, MySQL, Bootstrap 5, and JavaScript.

## Features

### Core Functionality
- **User Management**: Multi-role authentication (Super Admin, Principal, Teacher, Class Teacher)
- **Student Management**: Registration, editing, class assignment, and academic tracking
- **Results Management**: CA and exam marks entry with automatic grade calculation
- **Reporting System**: Comprehensive reports with PDF generation and Excel export
- **Academic Structure**: Support for Forms 1-6, terms, subjects, and academic years
- **Division Calculation**: Automatic calculation based on Tanzanian education system

### Technical Features
- **Responsive Design**: Bootstrap 5 with mobile-first approach
- **AJAX Integration**: Seamless user experience with real-time updates
- **Data Security**: SQL injection prevention, CSRF protection, password hashing
- **File Management**: Excel/CSV import/export capabilities
- **Activity Logging**: Comprehensive audit trail
- **Data Validation**: Client and server-side validation

## Technology Stack

- **Backend**: PHP 7.4+ (Object-oriented approach)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: Bootstrap 5.3.2, jQuery 3.7.1, Chart.js
- **Additional Libraries**: 
  - DataTables (table management)
  - PhpSpreadsheet (Excel handling)
  - TCPDF (PDF generation)

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- Web server (Apache/Nginx)
- Composer (for dependency management)

### Step 1: Clone/Download the Project
```bash
git clone <repository-url>
cd school-results-management
```

### Step 2: Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE school_results_db;
```

2. Import the database schema:
```bash
mysql -u root -p school_results_db < database.sql
```

3. Update database configuration in `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_results_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 3: File Permissions
Ensure the uploads directory is writable:
```bash
chmod 755 uploads/
```

### Step 4: Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### Step 5: Install Dependencies (Optional)
If using Composer for additional libraries:
```bash
composer install
```

## Configuration

### Environment Setup
1. Copy and modify configuration:
```bash
cp includes/config.php.example includes/config.php
```

2. Update settings in `includes/config.php`:
- Database credentials
- Base URL
- Upload paths
- Timezone

### Default Login Credentials
- **Email**: admin@school.com
- **Password**: password

**Important**: Change the default password immediately after first login.

## Project Structure

```
/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styles
│   ├── js/
│   │   └── app.js             # Main JavaScript functions
│   └── images/                # Image assets
├── includes/
│   ├── config.php             # Database and app configuration
│   ├── auth.php               # Authentication functions
│   ├── functions.php          # Common utility functions
│   ├── header.php             # Common header template
│   └── footer.php             # Common footer template
├── modules/
│   ├── admin/                 # Super admin modules
│   ├── principal/             # Principal modules
│   ├── teacher/               # Teacher modules
│   └── class-teacher/         # Class teacher modules
├── uploads/                   # File upload directory
├── database.sql               # Database schema
├── index.php                  # Main entry point
├── login.php                  # Login page
├── logout.php                 # Logout handler
└── README.md                  # This file
```

## User Roles & Permissions

### Super Admin
- Complete system access
- User management (create, edit, delete users)
- Student management (all operations)
- Subject and academic year management
- System settings and configuration
- Full reporting access
- Data import/export

### Principal
- View all students and results
- Access to comprehensive reports
- Analytics and performance dashboards
- Read-only access to system data

### Teacher
- Manage assigned subjects and classes
- Enter and edit results for assigned subjects
- View reports for assigned classes
- Student progress tracking

### Class Teacher
- Manage assigned class students
- View comprehensive class results
- Generate class reports
- Student academic monitoring

## Usage Guide

### Initial Setup
1. **Login** with admin credentials
2. **Create Academic Year** (Admin → Academic → Academic Years)
3. **Add Subjects** (Admin → Academic → Subjects)
4. **Create Users** (Admin → Users → Manage Users)
5. **Register Students** (Admin → Students → Manage Students)
6. **Assign Teachers** to subjects and classes

### Daily Operations
1. **Results Entry**: Teachers enter CA and exam marks
2. **Grade Calculation**: System automatically calculates grades and divisions
3. **Report Generation**: Generate progress reports and transcripts
4. **Data Export**: Export results to Excel for external use

### End of Term
1. **Complete Results Entry**: Ensure all marks are entered
2. **Generate Reports**: Create comprehensive term reports
3. **Calculate Rankings**: System calculates student positions
4. **Archive Data**: Export and backup term data

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` function
- **SQL Injection Prevention**: Prepared statements for all queries
- **CSRF Protection**: Token-based form protection
- **Session Security**: Secure session management
- **Input Validation**: Server and client-side validation
- **Activity Logging**: Complete audit trail
- **Role-based Access**: Granular permission system

## Backup and Maintenance

### Database Backup
```bash
mysqldump -u username -p school_results_db > backup_$(date +%Y%m%d).sql
```

### File Backup
```bash
tar -czf school_backup_$(date +%Y%m%d).tar.gz /path/to/school-results/
```

### Regular Maintenance
- Monitor disk space (uploads folder)
- Review activity logs
- Update user passwords regularly
- Clean old session files
- Backup data before major updates

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **File Upload Issues**
   - Check upload directory permissions
   - Verify PHP `upload_max_filesize` setting
   - Ensure `post_max_size` is adequate

3. **Login Problems**
   - Clear browser cache and cookies
   - Check user status in database
   - Verify password hasn't expired

4. **Performance Issues**
   - Enable PHP OPcache
   - Optimize database queries
   - Use CDN for static assets

### Log Files
- Check PHP error logs
- Review activity logs in database
- Monitor web server access logs

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review the troubleshooting section

## Changelog

### Version 1.0.0
- Initial release
- Complete user management system
- Student registration and management
- Results entry and calculation
- Basic reporting functionality
- PDF and Excel export capabilities

---

**Note**: This system is specifically designed for Tanzanian government secondary schools and follows the local education system structure and grading standards.
