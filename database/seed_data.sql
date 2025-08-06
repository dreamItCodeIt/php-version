-- Seed data for School Results Management System

-- Insert Super Admin
INSERT INTO users (name, email, password, role, phone) VALUES 
('System Administrator', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '+255123456789');

-- Insert Principal
INSERT INTO users (name, email, password, role, phone) VALUES 
('School Principal', 'principal@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'principal', '+255123456790');

-- Insert Teachers
INSERT INTO users (name, email, password, role, phone) VALUES 
('John Mwalimu', 'john@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '+255700123456'),
('Mary Shule', 'mary@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'class_teacher', '+255700123457'),
('Peter Elimu', 'peter@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '+255700123458'),
('Grace Masomo', 'grace@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '+255700123459');

-- Insert Academic Year
INSERT INTO academic_years (year, start_date, end_date, is_current) VALUES 
('2024', '2024-01-01', '2024-12-31', 1);

-- Insert Terms
INSERT INTO terms (name, academic_year_id, start_date, end_date, is_current) VALUES 
('Term 1', 1, '2024-01-01', '2024-06-30', 1),
('Term 2', 1, '2024-07-01', '2024-12-31', 0);

-- Insert O-Level Subjects
INSERT INTO subjects (name, code, level) VALUES 
('CIVICS', 'CIV', 'ordinary'),
('HISTORY', 'HIST', 'ordinary'),
('GEOGRAPHY', 'GEO', 'ordinary'),
('ED/KIISLAMU', 'IRE', 'ordinary'),
('KISWAHILI', 'KISW', 'ordinary'),
('ENGLISH', 'ENGL', 'ordinary'),
('PHYSICS', 'PHY', 'ordinary'),
('CHEMISTRY', 'CHEM', 'ordinary'),
('BIOLOGY', 'BIO', 'ordinary'),
('BASIC MATHEMATICS', 'B/MATH', 'ordinary'),
('CHINESE', 'CHI', 'ordinary'),
('COMMERCE', 'COMM', 'ordinary'),
('BOOK-KEEPING', 'B/KEEP', 'ordinary'),
('LITERATURE IN ENGLISH', 'LIT', 'ordinary'),
('FOOD AND HUMAN NUTRITION', 'FOOD', 'ordinary'),
('COMPUTER STUDIES', 'COMP', 'ordinary'),
('FINE ART', 'F.ART', 'ordinary'),
('FRENCH', 'FREN', 'ordinary'),
('TEXTILE AND GARMENT CONSTRUCTION', 'TEXT', 'ordinary'),
('BIBLICAL KNOWLEDGE', 'B/KNOW', 'ordinary');

-- Insert A-Level Subjects
INSERT INTO subjects (name, code, level) VALUES 
('GENERAL STUDIES', 'GS', 'advanced'),
('HISTORY', 'HIST', 'advanced'),
('GEOGRAPHY', 'GEO', 'advanced'),
('ISLAMIC KNOWLEDGE', 'IK', 'advanced'),
('ENGLISH LANGUAGE', 'ENG', 'advanced'),
('FRENCH LANGUAGE', 'FREN', 'advanced'),
('BASIC APPLIED MATHEMATICS', 'BAM', 'advanced'),
('ADVANCED MATHEMATICS', 'ADM', 'advanced'),
('ECONOMICS', 'ECON', 'advanced');

-- Insert Classes
INSERT INTO classes (name, form, level, academic_year_id, class_teacher_id) VALUES 
('Form 1A', 1, 'ordinary', 1, 4),
('Form 1B', 1, 'ordinary', 1, NULL),
('Form 2A', 2, 'ordinary', 1, NULL),
('Form 2B', 2, 'ordinary', 1, NULL),
('Form 3A', 3, 'ordinary', 1, NULL),
('Form 3B', 3, 'ordinary', 1, NULL),
('Form 4A', 4, 'ordinary', 1, NULL),
('Form 4B', 4, 'ordinary', 1, NULL),
('Form 5A', 5, 'advanced', 1, NULL),
('Form 6A', 6, 'advanced', 1, NULL);

-- Insert Sample Students
INSERT INTO students (admission_no, name, gender, date_of_birth, current_form, academic_year_id) VALUES 
('STD0001', 'Amina Hassan', 'female', '2008-03-15', 1, 1),
('STD0002', 'John Mwangi', 'male', '2008-07-22', 1, 1),
('STD0003', 'Fatuma Ali', 'female', '2007-11-08', 2, 1),
('STD0004', 'David Kimani', 'male', '2007-05-14', 2, 1),
('STD0005', 'Aisha Mohamed', 'female', '2006-09-30', 3, 1),
('STD0006', 'Michael Juma', 'male', '2006-12-18', 3, 1),
('STD0007', 'Zainab Omar', 'female', '2005-04-25', 4, 1),
('STD0008', 'Emmanuel Mushi', 'male', '2005-08-12', 4, 1),
('STD0009', 'Halima Salim', 'female', '2004-01-20', 5, 1),
('STD0010', 'Francis Mbogo', 'male', '2004-06-07', 6, 1);

-- Assign students to classes
INSERT INTO student_classes (student_id, class_id, academic_year_id) VALUES 
(1, 1, 1), (2, 1, 1), (3, 3, 1), (4, 3, 1), (5, 5, 1),
(6, 5, 1), (7, 7, 1), (8, 7, 1), (9, 9, 1), (10, 10, 1);

-- Assign subjects to students (O-Level students get multiple subjects)
INSERT INTO student_subjects (student_id, subject_id, academic_year_id) VALUES 
-- Form 1 students (basic subjects)
(1, 1, 1), (1, 2, 1), (1, 5, 1), (1, 6, 1), (1, 7, 1), (1, 8, 1), (1, 9, 1), (1, 10, 1),
(2, 1, 1), (2, 2, 1), (2, 5, 1), (2, 6, 1), (2, 7, 1), (2, 8, 1), (2, 9, 1), (2, 10, 1),
-- Form 2 students
(3, 1, 1), (3, 2, 1), (3, 5, 1), (3, 6, 1), (3, 7, 1), (3, 8, 1), (3, 9, 1), (3, 10, 1),
(4, 1, 1), (4, 2, 1), (4, 5, 1), (4, 6, 1), (4, 7, 1), (4, 8, 1), (4, 9, 1), (4, 10, 1);

-- Assign teachers to subjects
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year_id) VALUES 
(3, 6, 1), (3, 14, 1), -- John teaches English and Literature
(4, 10, 1), (4, 7, 1), -- Mary teaches Mathematics and Physics
(5, 8, 1), (5, 9, 1),  -- Peter teaches Chemistry and Biology
(6, 1, 1), (6, 2, 1);  -- Grace teaches Civics and History

-- Insert system settings
INSERT INTO settings (key, value, description) VALUES 
('school_name', 'Government Secondary School', 'Name of the school'),
('school_address', 'P.O. Box 123, Dar es Salaam, Tanzania', 'School address'),
('current_academic_year', '1', 'Current academic year ID'),
('current_term', '1', 'Current term ID'),
('grading_system', 'tanzania', 'Grading system used');
