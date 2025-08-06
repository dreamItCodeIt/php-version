    </div> <!-- End container-fluid -->
    
    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo APP_NAME; ?></h5>
                    <p class="mb-0">
                        <small>&copy; <?php echo date('Y'); ?> Government Secondary School. All rights reserved.</small>
                    </p>
                    <p class="mb-0">
                        <small>Version <?php echo APP_VERSION; ?></small>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <small>
                            <i class="bi bi-calendar-event me-1"></i>
                            Academic Year: 
                            <?php 
                            $currentYear = getCurrentAcademicYear();
                            echo $currentYear ? htmlspecialchars($currentYear['year']) : 'Not Set';
                            ?>
                        </small>
                    </p>
                    <p class="mb-0">
                        <small>
                            <i class="bi bi-clock me-1"></i>
                            Last login: <?php echo formatDateTime($currentUser['last_login'] ?? ''); ?>
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    
    <!-- DataTables (for table management) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Chart.js (for charts and analytics) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline JavaScript -->
    <?php if (isset($inlineScripts)): ?>
        <script>
            <?php echo $inlineScripts; ?>
        </script>
    <?php endif; ?>
    
    <script>
        // Common JavaScript functions and event handlers
        $(document).ready(function() {
            // Initialize DataTables on tables with data-table class
            if ($.fn.DataTable) {
                $('.data-table').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries per page",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
            }
            
            // Auto-hide alerts after 5 seconds
            $('.alert').each(function() {
                const alert = this;
                setTimeout(function() {
                    $(alert).fadeOut();
                }, 5000);
            });
            
            // Confirm delete actions
            $('.btn-delete').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
            
            // Form validation
            $('.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
            
            // CSRF token for AJAX requests
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                        xhr.setRequestHeader("X-CSRFToken", $('meta[name=csrf-token]').attr('content'));
                    }
                }
            });
            
            // Loading overlay functions
            window.showLoading = function() {
                if ($('#loading-overlay').length === 0) {
                    $('body').append(`
                        <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    `);
                }
            };
            
            window.hideLoading = function() {
                $('#loading-overlay').remove();
            };
            
            // Toast notification function
            window.showToast = function(message, type = 'info') {
                const toastHtml = `
                    <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0 position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;
                $('body').append(toastHtml);
                const toast = new bootstrap.Toast($('.toast').last()[0]);
                toast.show();
                
                // Remove toast element after it's hidden
                $('.toast').last().on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            };
        });
    </script>
</body>
</html>
