<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h3>Login</h3>
                        <form id="loginForm" method="POST" action="auth/login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">Password must be at least 8 characters.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
                <!-- <div class="card mt-3">
                    <div class="card-body">
                        <h3>Register</h3>
                        <form id="registerForm" method="POST" action="auth/register.php">
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="registerEmail" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="registerPassword" name="password" required>
                                <div class="invalid-feedback">Password must be at least 8 characters.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                        </form>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            // Client-side validation
            let isValid = true;
            const email = $('#email').val();
            const password = $('#password').val();

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                $('#email').addClass('is-invalid');
                isValid = false;
            } else {
                $('#email').removeClass('is-invalid');
            }

            if (password.length < 8) {
                $('#password').addClass('is-invalid');
                isValid = false;
            } else {
                $('#password').removeClass('is-invalid');
            }

            if (isValid) {
                $.ajax({
                    type: 'POST',
                    url: 'auth/login.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log(response.success);
                        if (response.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        console.log(response);
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        });

        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            // Client-side validation
            let isValid = true;
            const email = $('#registerEmail').val();
            const password = $('#registerPassword').val();

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                $('#registerEmail').addClass('is-invalid');
                isValid = false;
            } else {
                $('#registerEmail').removeClass('is-invalid');
            }

            if (password.length < 8) {
                $('#registerPassword').addClass('is-invalid');
                isValid = false;
            } else {
                $('#registerPassword').removeClass('is-invalid');
            }

            if (isValid) {
                $.ajax({
                    type: 'POST',
                    url: 'auth/register.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Registration successful. You can now log in.');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>