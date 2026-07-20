/**
 * assets/js/auth.js
 * Client-side validation for Login / Register forms.
 * Note: this is a UX layer only — the server re-validates
 * everything, since client-side checks can always be bypassed.
 */

function togglePasswordField(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function showFieldError(inputEl, message) {
    inputEl.classList.add('invalid');
    const errorEl = inputEl.closest('.form-group').querySelector('.field-error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }
}

function clearFieldError(inputEl) {
    inputEl.classList.remove('invalid');
    const errorEl = inputEl.closest('.form-group').querySelector('.field-error');
    if (errorEl) {
        errorEl.classList.remove('show');
    }
}

function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

document.addEventListener('DOMContentLoaded', () => {
    // ---- Register form validation ----
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            let valid = true;

            const name = document.getElementById('full_name');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');

            [name, email, password, confirm].forEach(clearFieldError);

            if (name.value.trim().length < 2) {
                showFieldError(name, 'Please enter your full name.');
                valid = false;
            }
            if (!isValidEmail(email.value.trim())) {
                showFieldError(email, 'Please enter a valid email address.');
                valid = false;
            }
            if (password.value.length < 8) {
                showFieldError(password, 'Password must be at least 8 characters.');
                valid = false;
            }
            if (confirm.value !== password.value) {
                showFieldError(confirm, 'Passwords do not match.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                return;
            }

            setSubmitLoading(registerForm, true);
        });
    }

    // ---- Login form validation ----
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let valid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            [email, password].forEach(clearFieldError);

            if (!isValidEmail(email.value.trim())) {
                showFieldError(email, 'Please enter a valid email address.');
                valid = false;
            }
            if (password.value.length === 0) {
                showFieldError(password, 'Please enter your password.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                return;
            }

            setSubmitLoading(loginForm, true);
        });
    }
});

function setSubmitLoading(form, isLoading) {
    const btn = form.querySelector('.btn-auth');
    const spinner = btn.querySelector('.spinner');
    const label = btn.querySelector('.btn-label');
    btn.disabled = isLoading;
    if (spinner) spinner.classList.toggle('show', isLoading);
    if (label) label.textContent = isLoading ? 'Please wait…' : label.dataset.original;
}
