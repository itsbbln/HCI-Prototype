// Basic JavaScript for frontend interactions
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    setupModals();
    
    // Form interactions
    setupForms();
});

function setupModals() {
    // Open modal
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    };
    
    // Close modal
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    };
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
}

function setupForms() {
    // Add any frontend form validation here
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Frontend validation can go here
            console.log('Form submitted');
        });
    });
}

// Attendance checkboxes
function setupAttendanceCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
}

function goToDashboard() {
    const user = getCurrentUser();
    if (!user) return window.location.href = '/HCI-Prototype/login.html';

    const dashboards = {
        teacher: '/HCI-Prototype/teacher/teacher-dashboard.html',
        prefect: '/HCI-Prototype/prefect/prefect-dashboard.html',
        student: '/HCI-Prototype/student/student-dashboard.html',
        beadle: '/HCI-Prototype/beadle/beadle-dashboard.html'
    };
    window.location.href = dashboards[user.role];
}

function navigateTo(page) {
    const user = getCurrentUser();
    if (!user) return logout();

    const base = '/HCI-Prototype/' + user.role + '/';
    window.location.href = base + page;
}