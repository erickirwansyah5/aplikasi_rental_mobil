<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Get customer information
$user_id = $_SESSION['user_id'];

$query = "SELECT users.*, customers.*
          FROM users
          INNER JOIN customers ON users.id = customers.user_id 
          WHERE users.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt); // Simpan hasil ke $result
$customer = mysqli_fetch_assoc($result); // Gunakan $result di sini



// Get active rentals count
$query = "SELECT COUNT(*) as active_rentals 
          FROM rentals 
          WHERE customer_id = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$active_rentals = mysqli_fetch_assoc($result)['active_rentals'];

// Get total rentals count
$query = "SELECT COUNT(*) as total_rentals 
          FROM rentals 
          WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_rentals = mysqli_fetch_assoc($result)['total_rentals'];

// Get latest rental
$query = "SELECT r.*, c.brand, c.model, c.license_plate 
          FROM rentals r 
          JOIN cars c ON r.car_id = c.id 
          WHERE r.customer_id = ? 
          ORDER BY r.created_at DESC 
          LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$latest_rental = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - CarRent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .welcome-banner {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/images/bg-car.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .latest-rental-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .car-image {
            height: 200px;
            object-fit: cover;
        }
        .quick-actions .btn {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">CarRent</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="catalog.php">Catalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_rentals.php">My Rentals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="container">
            <h1>Welcome, <?php echo htmlspecialchars($customer['name']); ?>!</h1>
            
            <p class="lead">Manage your car rentals and explore our catalog</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Statistics -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Rentals</h5>
                                <p class="card-text display-4"><?php echo $active_rentals; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Rentals</h5>
                                <p class="card-text display-4"><?php echo $total_rentals; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Latest Rental -->
                <?php if ($latest_rental): ?>
                <h4 class="mt-4 mb-3">Latest Rental</h4>
                <div class="card latest-rental-card">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="../assets/images/cars/<?php echo htmlspecialchars($latest_rental['license_plate']); ?>" 
                                 class="img-fluid car-image" 
                                 alt="<?php echo htmlspecialchars($latest_rental['brand'] . ' ' . $latest_rental['model']); ?>"
                                 onerror="this.src='../assets/images/car-placeholder.jpg'">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($latest_rental['brand'] . ' ' . $latest_rental['model']); ?>
                                </h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Rental Period: <?php echo date('d/m/Y', strtotime($latest_rental['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($latest_rental['end_date'])); ?>
                                    </small>
                                </p>
                                <p class="card-text">
                                    Status: 
                                    <span class="badge bg-<?php echo $latest_rental['status'] == 'active' ? 'success' : 
                                        ($latest_rental['status'] == 'completed' ? 'primary' : 'secondary'); ?>">
                                        <?php echo ucfirst($latest_rental['status']); ?>
                                    </span>
                                </p>
                                <p class="card-text">
                                    Total Amount: Rp <?php echo number_format($latest_rental['total_amount']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body quick-actions">
                        <a href="catalog.php" class="btn btn-primary w-100">
                            <i class="fas fa-car"></i> Browse Cars
                        </a>
                        <a href="my_rentals.php" class="btn btn-info w-100">
                            <i class="fas fa-list"></i> View My Rentals
                        </a>
                        <a href="profile.php" class="btn btn-secondary w-100">
                            <i class="fas fa-user"></i> Update Profile
                        </a>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">My Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($customer['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
