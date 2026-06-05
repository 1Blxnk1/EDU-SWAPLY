/**
 * Swaply - Client-side JavaScript
 * Form validation and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ---- Registration Form Validation ----
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors();
            
            // Full name
            const fullName = document.getElementById('full_name');
            if (fullName.value.trim().length < 3) {
                showError(fullName, 'Full name must be at least 3 characters');
                isValid = false;
            }
            
            // Email
            const email = document.getElementById('email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value.trim())) {
                showError(email, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Phone
            const phone = document.getElementById('phone');
            const phonePattern = /^0[0-9]{9}$/;
            if (!phonePattern.test(phone.value.trim())) {
                showError(phone, 'Please enter a valid 10-digit SA phone number (e.g. 0721234567)');
                isValid = false;
            }
            
            // SA ID Number - 13 digits
            const idNumber = document.getElementById('id_number');
            const idPattern = /^[0-9]{13}$/;
            if (!idPattern.test(idNumber.value.trim())) {
                showError(idNumber, 'Please enter a valid 13-digit South African ID number');
                isValid = false;
            }
            
            // Password
            const password = document.getElementById('password');
            const passPattern = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/;
            if (!passPattern.test(password.value)) {
                showError(password, 'Password must be 8+ chars with at least 1 number and 1 special character');
                isValid = false;
            }
            
            // Confirm password
            const confirmPass = document.getElementById('confirm_password');
            if (confirmPass.value !== password.value) {
                showError(confirmPass, 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ---- Login Form Validation ----
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors();
            
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            if (email.value.trim() === '') {
                showError(email, 'Please enter your email');
                isValid = false;
            }
            
            if (password.value === '') {
                showError(password, 'Please enter your password');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ---- Checkout Form Validation ----
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        // Luhn check - same logic as the PHP side
        function luhn(num) {
            const n = num.replace(/\D/g, '');
            if (n.length < 13 || n.length > 19) return false;
            let sum = 0, alt = false;
            for (let i = n.length - 1; i >= 0; i--) {
                let d = parseInt(n[i], 10);
                if (alt) { d *= 2; if (d > 9) d -= 9; }
                sum += d;
                alt = !alt;
            }
            return sum % 10 === 0;
        }

        checkoutForm.addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors();

            const address = document.getElementById('shipping_address');
            if (address.value.trim().length < 10) {
                showError(address, 'Please enter a complete shipping address (min 10 characters)');
                isValid = false;
            }

            const cardNum = document.getElementById('card_number');
            if (!luhn(cardNum.value)) {
                showError(cardNum, 'Card number is invalid');
                isValid = false;
            }

            const cardName = document.getElementById('card_name');
            if (cardName.value.trim().length < 2) {
                showError(cardName, 'Enter the cardholder name');
                isValid = false;
            }

            const exp = document.getElementById('card_expiry');
            const m = exp.value.match(/^(0[1-9]|1[0-2])\/(\d{2})$/);
            if (!m) {
                showError(exp, 'Expiry must be MM/YY');
                isValid = false;
            } else {
                const expYear = 2000 + parseInt(m[2], 10);
                const expMonth = parseInt(m[1], 10);
                const now = new Date();
                if (expYear < now.getFullYear() || (expYear === now.getFullYear() && expMonth < now.getMonth() + 1)) {
                    showError(exp, 'Card has expired');
                    isValid = false;
                }
            }

            const cvv = document.getElementById('card_cvv');
            if (!/^\d{3,4}$/.test(cvv.value)) {
                showError(cvv, 'CVV must be 3 or 4 digits');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ---- Review Form Validation ----
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors();
            
            const rating = document.querySelector('input[name="rating"]:checked');
            if (!rating) {
                alert('Please select a star rating');
                isValid = false;
            }
            
            const comment = document.getElementById('comment');
            if (comment.value.trim().length < 5) {
                showError(comment, 'Please write at least 5 characters');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // ---- Helper Functions ----
    function showError(inputElement, message) {
        const formGroup = inputElement.closest('.form-group');
        let errorDiv = formGroup.querySelector('.error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            formGroup.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        inputElement.style.borderColor = '#e74c3c';
    }
    
    function clearErrors() {
        document.querySelectorAll('.error').forEach(el => el.remove());
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.style.borderColor = '#ddd';
        });
    }
    
    // ---- Auto-dismiss alerts after 5 seconds ----
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
    
    // ---- Quantity buttons on cart page ----
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input[type="number"]');
            let val = parseInt(input.value);
            if (this.classList.contains('qty-plus')) {
                input.value = val + 1;
            } else if (this.classList.contains('qty-minus') && val > 1) {
                input.value = val - 1;
            }
        });
    });

    // ---- Mobile nav toggle ----
    const navToggle = document.querySelector('.nav-toggle');
    if (navToggle) {
        navToggle.addEventListener('click', function() {
            const navbar = document.querySelector('.navbar');
            navbar.classList.toggle('open');
            const expanded = navbar.classList.contains('open');
            navToggle.setAttribute('aria-expanded', expanded);
        });
    }

    // ---- Language dropdown: close on click outside ----
    document.addEventListener('click', function(e) {
        const switchers = document.querySelectorAll('.lang-switcher');
        switchers.forEach(switcher => {
            if (!switcher.contains(e.target)) {
                const dropdown = switcher.querySelector('.lang-dropdown');
                if (dropdown) dropdown.classList.remove('show');
            }
        });
    });
});
