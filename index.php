<?php
/**
 * CarRent - Car Rental Management System
 * 
 * @package     CarRent
 * @author      Erick Irwansyah
 * @version     1.0.0
 * @link        https://code80vity.com
 * @copyright   2024 Code80vity.com
 * 
 * This application is available for purchase at code80vity.com
 * For inquiries, please contact: info@code80vity.com
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: customer/index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CarRent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/bg-car.jpg');
            background-size: cover;
            background-position: center;
        }
        .welcome-container {
            text-align: center;
            color: white;
        }
        .login-options {
            margin-top: 50px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            margin: 15px;
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .login-card i {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .btn-lg {
            padding: 15px 30px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-container">
            <h1 class="display-4 mb-4">Welcome to CarRent</h1>
            <p class="lead mb-5">Your trusted car rental service</p>

            <div class="row justify-content-center login-options">
                <div class="col-md-4">
                    <div class="login-card">
                        <i class="fas fa-user-tie text-primary"></i>
                        <h3 class="text-success">Admin Login</h3>
                        <p class="text-success">Access the admin dashboard to manage cars, rentals, and customers.</p>
                        <a href="admin/login.php" class="btn btn-primary btn-lg">Admin Login</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="login-card">
                        <i class="fas fa-user text-success"></i>
                        <h3 class="text-success">Customer Login</h3>
                        <p class="text-success">Login to rent cars, view your rentals, and manage your profile.</p>
                        <a href="customer/login.php" class="btn btn-success btn-lg">Customer Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
