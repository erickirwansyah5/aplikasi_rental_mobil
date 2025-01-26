<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $id_card_number = mysqli_real_escape_string($conn, $_POST['id_card_number']);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert user
        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'customer')";
        mysqli_query($conn, $query);
        $user_id = mysqli_insert_id($conn);

        // Insert customer
        $query = "INSERT INTO customers (user_id, name, phone, email, address, id_card_number) 
                 VALUES ($user_id, '$name', '$phone', '$email', '$address', '$id_card_number')";
        mysqli_query($conn, $query);

        mysqli_commit($conn);
        header("Location: login.php?registered=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Registration failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRent - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #2980b9, #8e44ad);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-control {
            border-radius: 20px;
            padding: 10px 20px;
        }
        .btn-register {
            border-radius: 20px;
            padding: 10px 20px;
            background: linear-gradient(120deg, #2980b9, #8e44ad);
            border: none;
            width: 100%;
            color: white;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn-register:hover {
            opacity: 0.9;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register to CarRent</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" name="phone" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email">
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ID Card Number</label>
                <input type="text" class="form-control" name="id_card_number" required>
            </div>
            <button type="submit" class="btn btn-register">Register</button>
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
</body>
</html>
