function login() {
            const role = document.getElementById('role').value;

            // Redirect based on role
            switch(role) {
                case "teacher":
                    window.location.href = "teacher-dashboard.html";
                    break;
                case "prefect":
                    window.location.href = "prefect-dashboard.html";
                    break;
                case "student":
                    window.location.href = "student-dashboard.html";
                    break;
                case "beadle":
                    window.location.href = "beadle-dashboard.html";
                    break;
            }
        }

document.getElementById('selectAll').addEventListener('change', function() {
const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Individual checkbox management
const studentCheckboxes = document.querySelectorAll('.student-checkbox');
studentCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const selectAll = document.getElementById('selectAll');
        const allChecked = Array.from(studentCheckboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
    });
});