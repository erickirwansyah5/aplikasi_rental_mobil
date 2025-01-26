<?php
session_start();
include '../config/database.php';
// var_dump($_SESSION);
// die;
// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Get customer_id from database if not in session
if (!isset($_SESSION['customer_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id FROM customers WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['customer_id'] = $row['id'];
    } else {
        header("Location: login.php");
        exit();
    }
}

$customer_id = (int)$_SESSION['customer_id'];

// Check if customer_id is valid
$check_query = "SELECT id FROM customers WHERE id = $customer_id";
$check_result = mysqli_query($conn, $check_query);
if (!$check_result || mysqli_num_rows($check_result) === 0) {
    header("Location: login.php");
    exit();
}

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query
$query = "SELECT r.*, c.brand, c.model, c.image, 
          r.status as rental_status,
          p.id as payment_id,
          p.payment_status,
          p.payment_proof,
          p.created_at as payment_date,
          l.name as pickup_location,
          l.address as pickup_address
          FROM rentals r 
          INNER JOIN cars c ON r.car_id = c.id 
          LEFT JOIN payments p ON r.id = p.rental_id 
          LEFT JOIN pickup_locations l ON r.pickup_location_id = l.id
          WHERE r.customer_id = ?
          ORDER BY r.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Process rental cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_rental'])) {
    $rental_id = (int)$_POST['rental_id'];
    $car_id = (int)$_POST['car_id'];

    mysqli_begin_transaction($conn);

    try {
        // Update rental status
        $query = "UPDATE rentals SET status = 'cancelled' WHERE id = ? AND customer_id = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $rental_id, $customer_id);
        mysqli_stmt_execute($stmt);

        // Update car status
        $query = "UPDATE cars SET status = 'available' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $car_id);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
        $_SESSION['success_message'] = "Rental cancelled successfully.";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Failed to cancel rental. Please try again.";
    }

    header("Location: my_rentals.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rentals - CarRent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .rental-card {
            transition: transform 0.2s;
        }
        .rental-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .car-image {
            height: 200px;
            object-fit: cover;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .status-active {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: black;
        }
        .status-completed {
            background-color: #6c757d;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .payment-badge {
            position: absolute;
            top: 40px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .payment-pending {
            background-color: #ffc107;
            color: black;
        }
        .payment-confirmed {
            background-color: #28a745;
            color: white;
        }
        .payment-rejected {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">CarRent</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="catalog.php">Cars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_rentals.php">My Rentals</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">My Rentals</h2>

        <!-- Status filter buttons -->
        <div class="btn-group mb-4">
            <a href="?status=all" class="btn <?php echo !isset($_GET['status']) || $_GET['status'] === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                Semua Rental
            </a>
            <a href="?status=pending" class="btn <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                Menunggu Pembayaran
            </a>
            <a href="?status=active" class="btn <?php echo isset($_GET['status']) && $_GET['status'] === 'active' ? 'btn-success' : 'btn-outline-success'; ?>">
                Aktif
            </a>
            <a href="?status=completed" class="btn <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                Selesai
            </a>
            <a href="?status=cancelled" class="btn <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                Dibatalkan
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while ($rental = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="position-relative">
                                <img src="../assets/images/cars/<?php echo htmlspecialchars($rental['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?>"
                                     style="height: 200px; object-fit: cover;">
                                     
                                <?php
                                $status_class = '';
                                $status_text = '';
                                $payment_text = '';
                                
                                // Status Rental
                                switch($rental['rental_status']) {
                                    case 'pending':
                                        $status_class = 'bg-warning';
                                        $status_text = 'PENDING';
                                        break;
                                    case 'active':
                                        $status_class = 'bg-success';
                                        $status_text = 'ACTIVE';
                                        break;
                                    case 'completed':
                                        $status_class = 'bg-secondary';
                                        $status_text = 'COMPLETED';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'bg-danger';
                                        $status_text = 'CANCELLED';
                                        break;
                                }
                                
                                // Status Pembayaran
                                if (!$rental['payment_id']) {
                                    $payment_text = 'PAYMENT: UNPAID';
                                } else {
                                    switch($rental['payment_status']) {
                                        case 'pending':
                                            $payment_text = 'PAYMENT: PENDING';
                                            break;
                                        case 'confirmed':
                                            $payment_text = 'PAYMENT: CONFIRMED';
                                            break;
                                        case 'rejected':
                                            $payment_text = 'PAYMENT: REJECTED';
                                            break;
                                    }
                                }
                                ?>
                                
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge <?php echo $status_class; ?> me-1">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <span class="badge bg-info">
                                        <?php echo $payment_text; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?>
                                </h5>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-calendar"></i> Periode Rental:</strong><br>
                                    <?php 
                                    echo date('d M Y', strtotime($rental['start_date'])) . ' - ' . 
                                         date('d M Y', strtotime($rental['end_date'])); 
                                    ?>
                                </div>

                                <div class="mb-3">
                                    <strong><i class="fas fa-money-bill"></i> Total:</strong><br>
                                    Rp <?php echo number_format($rental['total_amount'], 0, ',', '.'); ?>
                                </div>

                                <?php if ($rental['pickup_location']): ?>
                                <div class="mb-3">
                                    <strong><i class="fas fa-map-marker-alt"></i> Lokasi Pengambilan:</strong><br>
                                    <?php echo htmlspecialchars($rental['pickup_location']); ?><br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($rental['pickup_address']); ?>
                                    </small>
                                </div>
                                <?php endif; ?>

                                <?php if ($rental['payment_proof']): ?>
                                <div class="mb-3">
                                    <strong><i class="fas fa-clock"></i> Tanggal Pembayaran:</strong><br>
                                    <?php echo date('d M Y H:i', strtotime($rental['payment_date'])); ?>
                                </div>
                                <?php endif; ?>

                                <?php if (($rental['payment_status'] === 'rejected' || !$rental['payment_id']) 
                                    && !in_array($rental['rental_status'], ['cancelled', 'completed'])): ?>
                                <div class="mt-3">
                                    <a href="upload_payment.php?rental_id=<?php echo $rental['id']; ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <?php if (isset($_GET['status'])): ?>
                    Tidak ada rental dengan status: <?php echo ucfirst($_GET['status']); ?>. 
                    <a href="?" class="alert-link">Lihat semua rental</a>
                <?php else: ?>
                    Anda belum memiliki rental. 
                    <a href="catalog.php" class="alert-link">Lihat katalog mobil</a> untuk mulai menyewa!
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
