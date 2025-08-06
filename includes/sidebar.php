<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/dashboard.php">
                    <i class="bi bi-house-door me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <?php if (hasRole(['super_admin', 'principal'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/students/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/students/">
                    <i class="bi bi-people me-2"></i>
                    Students
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasRole(['super_admin'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/teachers/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/teachers/">
                    <i class="bi bi-person-badge me-2"></i>
                    Teachers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/subjects/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/subjects/">
                    <i class="bi bi-book me-2"></i>
                    Subjects
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/classes/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/classes/">
                    <i class="bi bi-building me-2"></i>
                    Classes
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/results/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/results/">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Results
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/reports/">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Reports
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Settings</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/profile.php">
                    <i class="bi bi-person me-2"></i>
                    Profile
                </a>
            </li>
            
            <?php if (hasRole(['super_admin'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/settings.php">
                    <i class="bi bi-gear me-2"></i>
                    System Settings
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
