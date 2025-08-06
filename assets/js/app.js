/**
 * School Results Management System - Main JavaScript File
 */

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-dismiss alerts
    $('.alert-auto-dismiss').delay(5000).slideUp(300);
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
});

/**
 * Global application functions
 */
window.SchoolApp = {
    
    /**
     * Show loading overlay
     */
    showLoading: function() {
        if ($('#loading-overlay').length === 0) {
            $('body').append(`
                <div id="loading-overlay" class="loading-overlay">
                    <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
        $('#loading-overlay').fadeIn(200);
    },
    
    /**
     * Hide loading overlay
     */
    hideLoading: function() {
        $('#loading-overlay').fadeOut(200, function() {
            $(this).remove();
        });
    },
    
    /**
     * Show toast notification
     */
    showToast: function(message, type = 'info', duration = 5000) {
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'error' ? 'bg-danger' : 'bg-' + type;
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${this.getToastIcon(type)} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        $('body').append(toastHtml);
        const toastElement = new bootstrap.Toast($('#' + toastId)[0], {
            autohide: true,
            delay: duration
        });
        toastElement.show();
        
        // Remove toast element after it's hidden
        $('#' + toastId).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    },
    
    /**
     * Get appropriate icon for toast type
     */
    getToastIcon: function(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle',
            'primary': 'info-circle'
        };
        return icons[type] || 'info-circle';
    },
    
    /**
     * Confirm dialog with custom styling
     */
    confirm: function(message, title = 'Confirm Action', callback = null) {
        const modalId = 'confirm-modal-' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-question-circle me-2"></i>${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger confirm-yes">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        const modal = new bootstrap.Modal($('#' + modalId)[0]);
        
        $('#' + modalId + ' .confirm-yes').on('click', function() {
            modal.hide();
            if (callback && typeof callback === 'function') {
                callback(true);
            }
        });
        
        $('#' + modalId).on('hidden.bs.modal', function() {
            $(this).remove();
        });
        
        modal.show();
    },
    
    /**
     * AJAX form submission with loading and error handling
     */
    submitForm: function(form, options = {}) {
        const $form = $(form);
        const url = options.url || $form.attr('action');
        const method = options.method || $form.attr('method') || 'POST';
        const successCallback = options.success || function() {};
        const errorCallback = options.error || function() {};
        
        // Show loading
        this.showLoading();
        
        // Disable submit button
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Processing...');
        
        // Prepare form data
        const formData = new FormData(form);
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                SchoolApp.hideLoading();
                $submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    SchoolApp.showToast(response.message || 'Operation completed successfully', 'success');
                    successCallback(response);
                } else {
                    SchoolApp.showToast(response.message || 'An error occurred', 'error');
                    errorCallback(response);
                }
            },
            error: function(xhr, status, error) {
                SchoolApp.hideLoading();
                $submitBtn.prop('disabled', false).html(originalText);
                
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                SchoolApp.showToast(errorMessage, 'error');
                errorCallback({ message: errorMessage });
            }
        });
    },
    
    /**
     * Load content into container via AJAX
     */
    loadContent: function(url, container, showLoading = true) {
        const $container = $(container);
        
        if (showLoading) {
            $container.html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading content...</p>
                </div>
            `);
        }
        
        $.get(url)
            .done(function(data) {
                $container.html(data);
            })
            .fail(function() {
                $container.html(`
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted">Failed to load content. Please try again.</p>
                        <button class="btn btn-primary btn-sm" onclick="SchoolApp.loadContent('${url}', '${container}')">
                            <i class="bi bi-arrow-clockwise me-1"></i>Retry
                        </button>
                    </div>
                `);
            });
    },
    
    /**
     * Initialize DataTable with common settings
     */
    initDataTable: function(selector, options = {}) {
        const defaultOptions = {
            responsive: true,
            pageLength: 25,
            order: [],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: "No data available",
                zeroRecords: "No matching records found"
            },
            drawCallback: function() {
                // Re-initialize tooltips after table draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        };
        
        const finalOptions = $.extend(true, defaultOptions, options);
        return $(selector).DataTable(finalOptions);
    },
    
    /**
     * Format number with thousands separator
     */
    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    
    /**
     * Format date for display
     */
    formatDate: function(dateString, format = 'DD/MM/YYYY') {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        switch (format) {
            case 'DD/MM/YYYY':
                return `${day}/${month}/${year}`;
            case 'MM/DD/YYYY':
                return `${month}/${day}/${year}`;
            case 'YYYY-MM-DD':
                return `${year}-${month}-${day}`;
            default:
                return `${day}/${month}/${year}`;
        }
    },
    
    /**
     * Validate form inputs
     */
    validateForm: function(form) {
        const $form = $(form);
        let isValid = true;
        
        // Check required fields
        $form.find('[required]').each(function() {
            const $field = $(this);
            if (!$field.val().trim()) {
                $field.addClass('is-invalid');
                isValid = false;
            } else {
                $field.removeClass('is-invalid').addClass('is-valid');
            }
        });
        
        // Check email fields
        $form.find('input[type="email"]').each(function() {
            const $field = $(this);
            const email = $field.val().trim();
            if (email && !SchoolApp.isValidEmail(email)) {
                $field.addClass('is-invalid');
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    /**
     * Validate email format
     */
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    /**
     * Calculate grade from marks
     */
    calculateGrade: function(marks) {
        marks = parseFloat(marks) || 0;
        if (marks >= 80) return 'A';
        if (marks >= 70) return 'B';
        if (marks >= 60) return 'C';
        if (marks >= 50) return 'D';
        if (marks >= 40) return 'E';
        return 'F';
    },
    
    /**
     * Calculate grade points from marks
     */
    calculateGradePoints: function(marks) {
        marks = parseFloat(marks) || 0;
        if (marks >= 80) return 5;
        if (marks >= 70) return 4;
        if (marks >= 60) return 3;
        if (marks >= 50) return 2;
        if (marks >= 40) return 1;
        return 0;
    },
    
    /**
     * Get grade color class
     */
    getGradeColor: function(grade) {
        const colors = {
            'A': 'success',
            'B': 'primary',
            'C': 'warning',
            'D': 'secondary',
            'E': 'danger',
            'F': 'dark'
        };
        return colors[grade] || 'secondary';
    },
    
    /**
     * Print element content
     */
    printElement: function(elementId) {
        const printContent = document.getElementById(elementId);
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-size: 12px; }
                    .no-print { display: none !important; }
                    @media print {
                        .btn { display: none; }
                        .card { border: 1px solid #ddd; box-shadow: none; }
                    }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                ${printContent.innerHTML}
            </body>
            </html>
        `);
        
        printWindow.document.close();
    }
};

/**
 * Results Management specific functions
 */
window.ResultsManager = {
    
    /**
     * Auto-calculate total and grade when marks change
     */
    bindCalculations: function() {
        $(document).on('input', '.ca-marks, .exam-marks', function() {
            const $row = $(this).closest('tr');
            const caMarks = parseFloat($row.find('.ca-marks').val()) || 0;
            const examMarks = parseFloat($row.find('.exam-marks').val()) || 0;
            const total = caMarks + examMarks;
            const grade = SchoolApp.calculateGrade(total);
            
            $row.find('.total-marks').text(total.toFixed(1));
            $row.find('.grade-display')
                .text(grade)
                .removeClass('grade-A grade-B grade-C grade-D grade-E grade-F')
                .addClass('grade-' + grade);
        });
    },
    
    /**
     * Save results via AJAX
     */
    saveResults: function(form) {
        SchoolApp.submitForm(form, {
            success: function(response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            }
        });
    },
    
    /**
     * Bulk save results from table
     */
    bulkSaveResults: function(tableSelector) {
        const results = [];
        
        $(tableSelector + ' tbody tr').each(function() {
            const $row = $(this);
            const studentId = $row.data('student-id');
            const subjectId = $row.data('subject-id');
            const examinationId = $row.data('examination-id');
            const caMarks = parseFloat($row.find('.ca-marks').val()) || 0;
            const examMarks = parseFloat($row.find('.exam-marks').val()) || 0;
            
            if (studentId && subjectId && examinationId) {
                results.push({
                    student_id: studentId,
                    subject_id: subjectId,
                    examination_id: examinationId,
                    ca_marks: caMarks,
                    exam_marks: examMarks
                });
            }
        });
        
        if (results.length === 0) {
            SchoolApp.showToast('No results to save', 'warning');
            return;
        }
        
        SchoolApp.showLoading();
        
        $.ajax({
            url: 'api/save-bulk-results.php',
            method: 'POST',
            data: { results: results },
            dataType: 'json',
            success: function(response) {
                SchoolApp.hideLoading();
                if (response.success) {
                    SchoolApp.showToast(response.message || 'Results saved successfully', 'success');
                } else {
                    SchoolApp.showToast(response.message || 'Failed to save results', 'error');
                }
            },
            error: function() {
                SchoolApp.hideLoading();
                SchoolApp.showToast('An error occurred while saving results', 'error');
            }
        });
    }
};

// Initialize results calculations when document is ready
$(document).ready(function() {
    ResultsManager.bindCalculations();
});