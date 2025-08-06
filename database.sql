-- School Results Management System Database Schema
-- MySQL Database

-- Create database
CREATE DATABASE IF NOT EXISTS school_results_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_results_db;

-- User Management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','principal','teacher','class_teacher') NOT NULL,
    phone VARCHAR(20),
    status TINYINT DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Academic Years
CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year VARCHAR(10) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL,
    form ENUM('Form 1','Form 2','Form 3','Form 4','Form 5','Form 6','All') NOT NULL,
    category ENUM('Core','Optional') DEFAULT 'Core',
    max_marks INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender ENUM('Male','Female') NOT NULL,
    date_of_birth DATE,
    current_form ENUM('Form 1','Form 2','Form 3','Form 4','Form 5','Form 6') NOT NULL,
    stream VARCHAR(10) DEFAULT 'A',
    current_term ENUM('Term 1','Term 2') NOT NULL,
    academic_year_id INT,
    parent_name VARCHAR(100),
    parent_phone VARCHAR(20),
    parent_email VARCHAR(100),
    address TEXT,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Teachers Assignment to Subjects
CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    form ENUM('Form 1','Form 2','Form 3','Form 4','Form 5','Form 6') NOT NULL,
    stream VARCHAR(10) DEFAULT 'A',
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY unique_assignment (teacher_id, subject_id, form, stream, academic_year_id)
);

-- Class Teachers Assignment
CREATE TABLE class_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    form ENUM('Form 1','Form 2','Form 3','Form 4','Form 5','Form 6') NOT NULL,
    stream VARCHAR(10) DEFAULT 'A',
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY unique_class_teacher (form, stream, academic_year_id)
);

-- Examinations/Assessment Types
CREATE TABLE examinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('CA','Exam','Final') NOT NULL,
    academic_year_id INT NOT NULL,
    term ENUM('Term 1','Term 2') NOT NULL,
    form ENUM('Form 1','Form 2','Form 3','Form 4','Form 5','Form 6') NOT NULL,
    start_date DATE,
    end_date DATE,
    max_marks INT DEFAULT 100,
    weight DECIMAL(3,2) DEFAULT 1.00,
    status ENUM('Scheduled','In Progress','Completed') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
);

-- Student Results
CREATE TABLE student_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    examination_id INT NOT NULL,
    ca_marks DECIMAL(5,2) DEFAULT 0,
    exam_marks DECIMAL(5,2) DEFAULT 0,
    total_marks DECIMAL(5,2) GENERATED ALWAYS AS (ca_marks + exam_marks) STORED,
    grade VARCHAR(2),
    grade_points INT,
    remarks TEXT,
    entered_by INT,
    entered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (examination_id) REFERENCES examinations(id),
    FOREIGN KEY (entered_by) REFERENCES users(id),
    UNIQUE KEY unique_result (student_id, subject_id, examination_id)
);

-- Student Term Summary
CREATE TABLE student_term_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    term ENUM('Term 1','Term 2') NOT NULL,
    total_subjects INT DEFAULT 0,
    total_marks DECIMAL(8,2) DEFAULT 0,
    average_marks DECIMAL(5,2) DEFAULT 0,
    division VARCHAR(5),
    position INT,
    class_size INT,
    remarks TEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY unique_term_summary (student_id, academic_year_id, term)
);

-- Activity Logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- System Settings
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- File Uploads/Imports
CREATE TABLE file_imports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    import_type ENUM('students','results','subjects') NOT NULL,
    status ENUM('Pending','Processing','Completed','Failed') DEFAULT 'Pending',
    records_total INT DEFAULT 0,
    records_processed INT DEFAULT 0,
    records_failed INT DEFAULT 0,
    error_log TEXT,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES 
('System Administrator', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- Insert current academic year
INSERT INTO academic_years (year, start_date, end_date, is_current) VALUES 
('2024/2025', '2024-01-15', '2024-12-20', 1);

-- Insert default subjects for O-Level
INSERT INTO subjects (name, code, form, category, max_marks) VALUES
('Mathematics', 'MATH', 'All', 'Core', 100),
('English Language', 'ENG', 'All', 'Core', 100),
('Kiswahili', 'KIS', 'All', 'Core', 100),
('Chemistry', 'CHEM', 'All', 'Core', 100),
('Physics', 'PHY', 'All', 'Core', 100),
('Biology', 'BIO', 'All', 'Core', 100),
('Geography', 'GEO', 'All', 'Optional', 100),
('History', 'HIST', 'All', 'Optional', 100),
('Civics', 'CIV', 'All', 'Optional', 100),
('Book Keeping', 'BK', 'All', 'Optional', 100),
('Commerce', 'COM', 'All', 'Optional', 100),
('Computer Studies', 'ICT', 'All', 'Optional', 100);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('school_name', 'Government Secondary School', 'School Name'),
('school_address', 'Dar es Salaam, Tanzania', 'School Address'),
('school_phone', '+255 000 000 000', 'School Phone Number'),
('school_email', 'info@school.tz', 'School Email'),
('ca_percentage', '30', 'Continuous Assessment Percentage'),
('exam_percentage', '70', 'Final Exam Percentage'),
('passing_marks', '30', 'Minimum Passing Marks'),
('academic_year_start_month', '1', 'Academic Year Start Month (1-12)');

-- Create indexes for better performance
CREATE INDEX idx_students_form_stream ON students(current_form, stream);
CREATE INDEX idx_results_student_subject ON student_results(student_id, subject_id);
CREATE INDEX idx_results_examination ON student_results(examination_id);
CREATE INDEX idx_activity_logs_user_date ON activity_logs(user_id, created_at);
CREATE INDEX idx_teacher_subjects_teacher ON teacher_subjects(teacher_id);
CREATE INDEX idx_class_teachers_form ON class_teachers(form, stream, academic_year_id);