// Main JavaScript
// ...existing code or placeholder...
document.addEventListener('DOMContentLoaded', function() {
    // Login/Register Modal Functions
    const loginBtn = document.getElementById('login-btn');
    const closeModal = document.getElementById('close-modal');
    const authModal = document.getElementById('auth-modal');
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginSubmit = document.getElementById('login-submit');
    const registerSubmit = document.getElementById('register-submit');
    
    // Modal toggle functions
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            authModal.style.display = 'flex';
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            authModal.style.display = 'none';
        });
    }
    
    // Switch between login and register forms
    if (showRegister) {
        showRegister.addEventListener('click', function() {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        });
    }
    
    if (showLogin) {
        showLogin.addEventListener('click', function() {
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === authModal) {
            authModal.style.display = 'none';
        }
    });
    
    // Handle form submissions
    if (loginSubmit) {
        loginSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            // Validate inputs
            if (!email || !password) {
                alert('Please fill in all fields');
                return;
            }
            
            // AJAX call to login
            fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'login',
                    email: email,
                    password: password
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            });
        });
    }
    
    if (registerSubmit) {
        registerSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const childName = document.getElementById('child-name').value;
            const childAge = document.getElementById('child-age').value;
            
            // Validate inputs
            if (!name || !email || !password || !childName || !childAge) {
                alert('Please fill in all fields');
                return;
            }
            
            // AJAX call to register
            fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'register',
                    name: name,
                    email: email,
                    password: password,
                    childName: childName,
                    childAge: childAge
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registration successful! Please log in.');
                    registerForm.style.display = 'none';
                    loginForm.style.display = 'block';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            });
        });
    }
    
    // Scroll to categories section when explore button is clicked
    const exploreBtn = document.getElementById('explore-btn');
    if (exploreBtn) {
        exploreBtn.addEventListener('click', function() {
            document.getElementById('categories').scrollIntoView({
                behavior: 'smooth'
            });
        });
    }
});

function startTest(category) {
    // Redirect to the test data API with the selected category
    fetch(`api/test_data.php?category=${encodeURIComponent(category)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log(data); // Debugging: Log the response
                // Redirect to the test interface or handle the test data
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again later.');
        });
}