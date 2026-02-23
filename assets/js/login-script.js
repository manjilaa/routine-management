// Credentials - Hardcoded users
const users = [
    // Admin
    { 
        id: 'admin1', 
        email: 'admin@university.edu', 
        password: 'admin123', 
        name: 'Admin User', 
        role: 'admin' 
    },
    // Teachers
    { 
        id: 't1', 
        email: 'teacher@university.edu', 
        password: 'teacher123', 
        name: 'Dr. Sarah Johnson', 
        role: 'teacher',
        department: 'Computer Science'
    },
    { 
        id: 't2', 
        email: 'michael.chen@university.edu', 
        password: 'teacher123', 
        name: 'Prof. Michael Chen', 
        role: 'teacher',
        department: 'Computer Science'
    },
    { 
        id: 't3', 
        email: 'emily.davis@university.edu', 
        password: 'teacher123', 
        name: 'Dr. Emily Davis', 
        role: 'teacher',
        department: 'Computer Science'
    },
    { 
        id: 't4', 
        email: 'robert.wilson@university.edu', 
        password: 'teacher123', 
        name: 'Prof. Robert Wilson', 
        role: 'teacher',
        department: 'Electrical Engineering'
    },
    { 
        id: 't5', 
        email: 'lisa.anderson@university.edu', 
        password: 'teacher123', 
        name: 'Dr. Lisa Anderson', 
        role: 'teacher',
        department: 'Electrical Engineering'
    }
];

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    
    // Password toggle functionality
    const passwordToggle = document.getElementById('passwordToggle');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                passwordToggle.textContent = 'Show';
            }
        });
    }
    
    // Form submission
    const authForm = document.getElementById('authForm');
    if (authForm) {
        authForm.addEventListener('submit', handleAuth);
    }
    
    // Student view button
    const studentViewBtn = document.getElementById('studentViewBtn');
    if (studentViewBtn) {
        studentViewBtn.addEventListener('click', goToStudentView);
    }
});

// Authentication function
function authenticateUser(email, password) {
    return users.find(user => user.email === email && user.password === password);
}

// Handle Authentication
function handleAuth(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('errorMessage');
    
    // Authenticate user
    const user = authenticateUser(email, password);
    
    if (!user) {
        errorMessage.textContent = 'Invalid email or password';
        errorMessage.style.display = 'block';
        return;
    }
    
    // Hide error message
    errorMessage.style.display = 'none';
    
    // Store user in localStorage
    localStorage.setItem('currentUser', JSON.stringify(user));
    
    // Redirect based on role
    if (user.role === 'admin') {
        window.location.href = 'admin-dashboard.html';
    } else if (user.role === 'teacher') {
        window.location.href = 'teacher-dashboard.html';
    }
}

// Go to student view
function goToStudentView() {
    window.location.href = 'student-dashboard.html';
}