<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$rental_id = isset($_GET['rental_id']) ? (int)$_GET['rental_id'] : 0;
$success_message = '';
$error_message = '';

// Verify rental belongs to user
$query = "SELECT r.*, c.brand, c.model, c.daily_rate, p.payment_status, p.payment_proof 
          FROM rentals r 
          JOIN cars c ON r.car_id = c.id 
          LEFT JOIN payments p ON r.id = p.rental_id
          WHERE r.id = ? AND r.customer_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $rental_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$rental = mysqli_fetch_assoc($result)) {
    header("Location: my_rentals.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_location_id = (int)$_POST['pickup_location'];
    
    // Handle file upload
    if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['proof_of_payment']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newname = 'payment_' . $rental_id . '_' . time() . '.' . $filetype;
            $uploaddir = '../assets/payments/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploaddir)) {
                mkdir($uploaddir, 0777, true);
            }
            
            $uploadfile = $uploaddir . $newname;
            
            if (move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $uploadfile)) {
                mysqli_begin_transaction($conn);
                
                try {
                    // Check if payment record exists
                    $query = "SELECT id FROM payments WHERE rental_id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $rental_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($result) > 0) {
                        // Update existing payment
                        $query = "UPDATE payments SET 
                                 payment_proof = ?, 
                                 payment_status = 'pending'
                                 WHERE rental_id = ?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "si", $newname, $rental_id);
                        mysqli_stmt_execute($stmt);
                    } else {
                        // Create new payment record
                        $query = "INSERT INTO payments (rental_id, amount, payment_proof, payment_status) 
                                 SELECT ?, total_amount, ?, 'pending'
                                 FROM rentals WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "isi", $rental_id, $newname, $rental_id);
                        mysqli_stmt_execute($stmt);
                    }

                    // Update rental pickup location
                    $query = "UPDATE rentals SET 
                             pickup_location_id = ?,
                             status = 'pending'
                             WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ii", $pickup_location_id, $rental_id);
                    mysqli_stmt_execute($stmt);

                    mysqli_commit($conn);
                    $_SESSION['success_message'] = "Payment proof uploaded successfully! We will verify your payment shortly.";
                    header("Location: my_rentals.php");
                    exit();
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error_message = "Failed to process payment: " . $e->getMessage();
                }
            } else {
                $error_message = "Failed to upload file. Please try again.";
            }
        } else {
            $error_message = "Invalid file type. Allowed types: " . implode(', ', $allowed);
        }
    } else {
        $error_message = "Please select a file to upload.";
    }
}

// Get pickup locations
$query = "SELECT * FROM pickup_locations";
$pickup_locations = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment - CarRent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }
        .location-card {
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .location-card:hover {
            background-color: #f8f9fa;
        }
        .location-card.selected {
            background-color: #e9ecef;
            border-color: #0d6efd;
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
                        <a class="nav-link" href="my_rentals.php">My Rentals</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Rental Details</h5>
                        <p><strong>Car:</strong> <?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?></p>
                        <p><strong>Start Date:</strong> <?php echo date('d M Y', strtotime($rental['start_date'])); ?></p>
                        <p><strong>End Date:</strong> <?php echo date('d M Y', strtotime($rental['end_date'])); ?></p>
                        <p><strong>Total Amount:</strong> Rp <?php echo number_format($rental['total_amount'], 0, ',', '.'); ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst($rental['status']); ?></p>
                        
                        <?php if (empty($rental['payment_proof'])): ?>
                            <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
                                <div class="mb-3">
                                    <label class="form-label">Upload Payment Proof</label>
                                    <input type="file" class="form-control" name="proof_of_payment" required>
                                    <div class="form-text">Accepted formats: JPG, JPEG, PNG, PDF</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Pickup Location</label>
                                    <div id="map"></div>
                                    <div class="mt-3">
                                        <?php while ($location = mysqli_fetch_assoc($pickup_locations)): ?>
                                            <div class="card mb-2 location-card" data-location-id="<?php echo $location['id']; ?>"
                                                 data-lat="<?php echo $location['latitude']; ?>"
                                                 data-lng="<?php echo $location['longitude']; ?>">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="pickup_location" 
                                                               value="<?php echo $location['id']; ?>" required>
                                                        <label class="form-check-label">
                                                            <strong><?php echo htmlspecialchars($location['name']); ?></strong><br>
                                                            <?php echo htmlspecialchars($location['address']); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Payment Proof</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Payment proof has been uploaded. Status: <?php echo ucfirst($rental['payment_status']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Instructions</h5>
                        <p>Please transfer the total amount to one of our bank accounts:</p>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <strong>Bank BCA</strong><br>
                                Account Number: 1234567890<br>
                                Account Name: PT Car Rental
                            </li>
                            <li class="mb-3">
                                <strong>Bank Mandiri</strong><br>
                                Account Number: 0987654321<br>
                                Account Name: PT Car Rental
                            </li>
                        </ul>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> After making the payment, please upload your payment proof (transfer receipt) using the form.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([-6.200000, 106.816666], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var markers = [];
        
        $('.location-card').on('click', function() {
            var locationId = $(this).data('location-id');
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');
            
            // Select the radio button
            $(this).find('input[type="radio"]').prop('checked', true);
            
            // Highlight selected card
            $('.location-card').removeClass('selected');
            $(this).addClass('selected');
            
            // Clear existing markers
            markers.forEach(function(marker) {
                map.removeLayer(marker);
            });
            markers = [];
            
            // Add new marker
            var marker = L.marker([lat, lng]).addTo(map);
            markers.push(marker);
            
            // Center map on marker
            map.setView([lat, lng], 13);
        });
    </script>
</body>
</html>
