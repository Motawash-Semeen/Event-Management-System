<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1f2937;
            --error: #ef4444;
        }

        body {
            background: var(--background);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .auth-container {
            max-width: 400px;
            margin: 2rem auto;
            perspective: 1000px;
        }

        .auth-forms {
            position: relative;
            transition: transform 0.6s;
            transform-style: preserve-3d;
            min-height: 440px;
        }

        .form-container {
            position: absolute;
            width: 100%;
            backface-visibility: hidden;
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .login-form {
            z-index: 2;
            transform: rotateY(0deg);
        }

        .register-form {
            transform: rotateY(180deg);
        }

        .auth-forms.flipped {
            transform: rotateY(180deg);
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            z-index: 2;
        }

        .btn-auth {
            width: 100%;
            background: var(--primary);
            border: none;
            border-radius: 0.75rem;
            padding: 0.875rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-auth:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .auth-toggle {
            text-align: center;
            margin-top: 1.5rem;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            padding: 0;
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            color: var(--primary-hover);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="text-center mb-4">
                <h1 class="h3 mb-3 fw-bold text-primary" id="formTitle">Welcome Back</h1>
                <p class="text-muted" id="formSubtitle">Please sign in to continue</p>
            </div>

            <div class="auth-forms">
                <div class="form-container login-form">
                    <div class="card-body p-4">
                        <form id="loginForm" method="POST" action="auth/login.php" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="password-field">
                                    <input type="password" class="form-control" name="password" required>
                                    <i class="password-toggle fas fa-eye"></i>
                                </div>
                                <div class="invalid-feedback">Password must be at least 8 characters</div>
                            </div>

                            <button type="submit" class="btn btn-auth">Sign In</button>
                        </form>
                        <div class="auth-toggle">
                            <p class="mb-0">Don't have an account? 
                                <button type="button" class="toggle-btn" id="showRegister">Sign up</button>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-container register-form">
                    <div class="card-body p-4">
                        <form id="registerForm" method="POST" action="auth/register.php" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="username" required>
                                <div class="invalid-feedback">Please enter your name</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="password-field">
                                    <input type="password" class="form-control" name="password" required>
                                    <i class="password-toggle fas fa-eye"></i>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar"></div>
                                </div>
                                <div class="invalid-feedback">Password must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirm Password</label>
                                <div class="password-field">
                                    <input type="password" class="form-control" name="confirm_password" required>
                                    <i class="password-toggle fas fa-eye"></i>
                                </div>
                                <div class="invalid-feedback">Passwords do not match</div>
                            </div>

                            <button type="submit" class="btn btn-auth">Create Account</button>
                        </form>
                        <div class="auth-toggle">
                            <p class="mb-0">Already have an account? 
                                <button type="button" class="toggle-btn" id="showLogin">Sign in</button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#showRegister').click(function() {
            $('.auth-forms').addClass('flipped');
            $('#formTitle').text('Create Account');
            $('#formSubtitle').text('Please fill in your details');
        });

        $('#showLogin').click(function() {
            $('.auth-forms').removeClass('flipped');
            $('#formTitle').text('Welcome Back');
            $('#formSubtitle').text('Please sign in to continue');
        });

        $('.password-toggle').click(function() {
            const input = $(this).siblings('input');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        $('form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            
            if (validateForm(form)) {
                const formData = form.serialize();
                const url = form.attr('action');
                
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            showError(form, response.message);
                        }
                    },
                    error: function() {
                        showError(form, 'An error occurred. Please try again.');
                    }
                });
            }
        });

        function validateForm(form) {
            let isValid = true;
            const email = form.find('input[type="email"]');
            const password = form.find('input[type="password"]');

            if (!email.val().match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                email.addClass('is-invalid');
                isValid = false;
            } else {
                email.removeClass('is-invalid');
            }

            if (password.val().length < 8) {
                password.addClass('is-invalid');
                isValid = false;
            } else {
                password.removeClass('is-invalid');
            }

            return isValid;
        }

        function showError(form, message) {
            const alert = $(`<div class="alert alert-danger mb-3">${message}</div>`);
            form.prepend(alert);
            setTimeout(() => alert.fadeOut(() => alert.remove()), 3000);
        }
    });
    </script>
</body>
</html>