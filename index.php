<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKADEMIKA | Academic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/sosoklogin.css">
</head>
<body>
    <div class="login-body">
        <div class="container-wrapper" id="main-container">
            
            <!-- REGISTER FORM -->
            <div class="form-container register-container">
                <form class="h-100" action="auth.php" method="POST">
                    <h2 class="fw-bold mb-4">Create Account</h2>
                    
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-user-plus"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="register-password" name="password" placeholder="Password" required>
                        <span class="input-group-text" id="toggle-register-password" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold mt-3" name="register">Register</button>
                </form>
            </div>

            <!-- LOGIN FORM -->
            <div class="form-container login-container">
                <form class="h-100" action="auth.php" method="POST">
                    <h2 class="fw-bold mb-4">Welcome SIAKADEMIKA</h2>
                    
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="identifier" placeholder="Username or Email" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="login-password" name="password" placeholder="Password" required>
                        <span class="input-group-text" id="toggle-login-password" style="cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="text-decoration-none">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold" name="login">Login</button>
                </form>
            </div>

            <!-- OVERLAY -->
            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1 class="fw-bold text-white">Welcome Back!</h1>
                        <p class="text-white">Already have an account? Please login with your personal info.</p>
                        <button class="btn btn-outline-light fw-bold" id="btn-show-login">Login</button>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1 class="fw-bold text-white">Hello, User!</h1>
                        <p class="text-white">New here? Enter your personal details and start your journey.</p>
                        <button class="btn btn-outline-light fw-bold" id="btn-show-register">Sign Up</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sosoklogin.js"></script>
</body>
</html>
