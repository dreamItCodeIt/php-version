<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Student.php';
require_once '../classes/ClassModel.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Edit Student';
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    redirect('index.php');
}

$studentObj = new Student();
$classObj = new ClassModel();

$student = $studentObj->getById($student_id);
$classes = $classObj->getActive();

if (!$student) {
    redirect('index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $registration_number = trim($_POST['registration_number'] ?? '');
    $class_id = $_POST['class_id'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $guardian_name = trim($_POST['guardian_name'] ?? '');
    $guardian_phone = trim($_POST['guardian_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($name)) {
        $errors[] = 'Student name is required.';
    }

    if (empty($registration_number)) {
        $errors[] = 'Registration number is required.';
    }

    if (empty($gender)) {
        $errors[] = 'Gender is required.';
    }

    if (empty($date_of_birth)) {
        $errors[] = 'Date of birth is required.';
    }

    // Check if registration number exists for other students
    if ($studentObj->registrationExists($registration_number, $student_id)) {
        $errors[] = 'Registration number already exists.';
    }

    if (empty($errors)) {
        $data = [
            'name' => $name,
            'registration_number' => $registration_number,
            'class_id' => $class_id ?: null,
            'gender' => $gender,
            'date_of_birth' => $date_of_birth,
            'guardian_name' => $guardian_name,
            'guardian_phone' => $guardian_phone,
            'address' => $address,
            'is_active' => $is_active
        ];

        if ($studentObj->update($student_id, $data)) {
            $success = 'Student updated successfully!';
            $student = $studentObj->getById($student_id); // Refresh data
        } else {
            $errors[] = 'Failed to update student.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Student</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Students
                </a>
                <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View Student
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($student['name']); ?>" required>
