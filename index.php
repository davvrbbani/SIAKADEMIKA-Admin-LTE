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
<div class="container-wrapper d-flex" id="main-container">

    <!-- LEFT IMAGE SIDE -->
    <div class="image-container">
        <img src="assets/img/logoAngkatan2024-min.png" alt="Login Banner">
    </div>

    <!-- RIGHT LOGIN FORM -->
    <div class="form-container login-container">
        <form action="auth.php" method="POST">
            <h2 class="fw-bold mb-4 text-start">Welcome<br><span class="text-primary">SIAKADEMIKA</span></h2>

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

            <div class="d-flex justify-content-between align-items-center mb-3 w-100">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <a href="#" class="text-decoration-none">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold" name="login">Login</button>
        </form>
    </div>

</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sosoklogin.js"></script>
</body>
</html>