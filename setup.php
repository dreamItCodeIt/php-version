<?php
require_once 'config/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        
        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        $statements = explode(';', $schema);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $db->query($statement);
            }
        }
        
        // Read and execute seed data
        $seedData = file_get_contents(__DIR__ . '/database/seed_data.sql');
        $statements = explode(';', $seedData);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $db->query($statement);
            }
        }
        
        $message = 'Database setup completed successfully! You can now login with the demo credentials.';
        
    } catch (Exception $e) {
        $error = 'Setup failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-gear-fill me-2"></i>
                            School Results Management System Setup
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $message; ?>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    Go to Login
                                </a>
                            </div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                This will set up the database with sample data. Click the button below to proceed.
                            </div>
                            
                            <h5>What will be created:</h5>
                            <ul>
                                <li>Database tables for users, students, subjects, results, etc.</li>
                                <li>Sample users (Admin, Principal, Teachers)</li>
                                <li>Academic year and terms</li>
                                <li>O-Level and A-Level subjects</li>
                                <li>Sample students and classes</li>
                                <li>System settings</li>
                            </ul>
                            
                            <h5>Demo Login Credentials:</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>Password</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Super Admin</td>
                                            <td>admin@school.com</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Principal</td>
                                            <td>principal@school.com</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Teacher</td>
                                            <td>john@school.com</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Class Teacher</td>
                                            <td>mary@school.com</td>
                                            <td>password</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-play-circle me-2"></i>
                                        Setup Database
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
