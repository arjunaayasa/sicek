/**
 * Phishing Detector - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling with loading state
    const scanForm = document.querySelector('form');
    const submitButton = scanForm?.querySelector('button[type="submit"]');
    const submitButtonText = submitButton?.innerHTML || '';
    
    if (scanForm && submitButton) {
        scanForm.addEventListener('submit', function() {
            // Show loading state
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            submitButton.disabled = true;
            
            // Add loading class to form
            scanForm.classList.add('loading');
            
            // Form will submit normally
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // URL validation helper
    const urlInput = document.querySelector('input[name="url"]');
    if (urlInput) {
        urlInput.addEventListener('blur', function() {
            let url = this.value.trim();
            
            // Add http:// if no protocol is specified
            if (url && url.length > 0 && !url.match(/^https?:\/\//i)) {
                this.value = 'http://' + url;
            }
        });
    }
    
    // Copy result to clipboard functionality
    const copyButtons = document.querySelectorAll('.btn-copy');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy-text');
            if (textToCopy) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    // Show success message
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check-lg me-1"></i>Disalin';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            }
        });
    });
    
    // Add history toggle functionality
    const historyToggle = document.getElementById('historyToggle');
    const historySection = document.getElementById('historySection');
    
    if (historyToggle && historySection) {
        historyToggle.addEventListener('click', function() {
            if (historySection.classList.contains('d-none')) {
                historySection.classList.remove('d-none');
                this.innerHTML = '<i class="bi bi-chevron-up me-1"></i>Sembunyikan Riwayat';
            } else {
                historySection.classList.add('d-none');
                this.innerHTML = '<i class="bi bi-chevron-down me-1"></i>Tampilkan Riwayat';
            }
        });
    }
});