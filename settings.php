<?php
require_once 'config/config.php';
requireLogin();
requireRole(['super_admin']);

$user = getCurrentUser();
$db = Database::getInstance();

$message = '';
$error = '';

// Get current settings
$settings = [];
$settingsData = $db->fetchAll("SELECT * FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// Get academic years and terms
$academicYears = $db->fetchAll("SELECT * FROM academic_years ORDER BY year DESC");
$terms = $db->fetchAll("SELECT * FROM terms ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_settings') {
            try {
                $db->beginTransaction();
                
                $settingsToUpdate = [
                    'school_name' => sanitize($_POST['school_name']),
                    'school_address' => sanitize($_POST['school_address']),
                    'current_academic_year' => (int)$_POST['current_academic_year'],
                    'current_term' => (int)$_POST['current_term']
                ];
                
                foreach ($settingsToUpdate as $key => $value) {
                    $existing = $db->fetchOne("SELECT id FROM settings WHERE key = ?", [$key]);
                    if
                    $existing = $db->fetchOne("SELECT id FROM settings WHERE key = ?", [$key]);
                    if ($existing) {
                        $db->query("UPDATE settings SET value = ? WHERE key = ?", [$value, $key]);
                    } else {
                        $db->query("INSERT INTO settings (key, value) VALUES (?, ?)", [$key, $value]);
                    }
                }
                
                // Update academic year and term current status
                $db->query("UPDATE academic_years SET is_current = 0");
                $db->query("UPDATE academic_years SET is_current = 1 WHERE id = ?", [$_POST['current_academic_year']]);
                
                $db->query("UPDATE terms SET is_current = 0");
                $db->query("UPDATE terms SET is_current = 1 WHERE id = ?", [$_POST['current_term']]);
                
                $db->commit();
                $message = 'Settings updated successfully!';
                
                // Refresh settings
                $settingsData = $db->fetchAll("SELECT * FROM settings");
                $settings = [];
                foreach ($settingsData as $setting) {
                    $settings[$setting['key']] = $setting['value'];
                }
                
            } catch (Exception $e) {
                $db->rollback();
                $error = 'Failed to update settings. Please try again.';
            }
        }
    }
}

$pageTitle = 'System Settings';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-gear me-2"></i>System Settings
                </h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- System Settings Form -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">General Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="school_name" class="form-label">School Name</label>
                                    <input type="text" class="form-control" id="school_name" name="school_name" 
                                           value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="school_address" class="form-label">School Address</label>
                                    <textarea class="form-control" id="school_address" name="school_address" rows="3"><?php echo htmlspecialchars($settings['school_address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_academic_year" class="form-label">Current Academic Year</label>
                                    <select class="form-select" id="current_academic_year" name="current_academic_year" required>
                                        <option value="">Select Academic Year</option>
                                        <?php foreach ($academicYears as $year): ?>
                                        <option value="<?php echo $year['id']; ?>" 
                                                <?php echo ($year['is_current'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($year['year']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="current_term" class="form-label">Current Term</label>
                                    <select class="form-select" id="current_term" name="current_term" required>
                                        <option value="">Select Term</option>
                                        <?php foreach ($terms as $term): ?>
                                        <option value="<?php echo $term['id']; ?>" 
                                                <?php echo ($term['is_current'] ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($term['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- System Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Application Name:</strong> <?php echo APP_NAME; ?></p>
                            <p><strong>Version:</strong> <?php echo APP_VERSION; ?></p>
                            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Database:</strong> SQLite</p>
                            <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                            <p><strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Statistics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Database Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <?php
                        $stats = [
                            'Users' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'],
                            'Students' => $db->fetchOne("SELECT COUNT(*) as count FROM students")['count'],
                            'Subjects' => $db->fetchOne("SELECT COUNT(*) as count FROM subjects")['count'],
                            'Classes' => $db->fetchOne("SELECT COUNT(*) as count FROM classes")['count'],
                            'Results' => $db->fetchOne("SELECT COUNT(*) as count FROM results")['count'],
                            'Divisions' => $db->fetchOne("SELECT COUNT(*) as count FROM student_divisions")['count']
                        ];
                        ?>
                        
                        <?php foreach ($stats as $label => $count): ?>
                        <div class="col-md-2 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="h5 font-weight-bold text-primary"><?php echo $count; ?></div>
                                    <div class="text-xs text-uppercase text-muted"><?php echo $label; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
