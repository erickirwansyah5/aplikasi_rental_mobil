<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all cars with their rental status
$query = "SELECT c.*, 
          CASE 
              WHEN EXISTS (
                  SELECT 1 FROM rentals r 
                  WHERE r.car_id = c.id 
                  AND r.status = 'active'
              ) THEN 'rented'
              ELSE 'available'
          END as availability_status
          FROM cars c
          ORDER BY c.brand, c.model";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Catalog - CarRent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .car-card {
            height: 100%;
            transition: transform 0.2s;
        }
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
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
        .status-available {
            background-color: #28a745;
            color: white;
        }
        .status-rented {
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
                        <a class="nav-link active" href="catalog.php">Cars</a>
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
        <h2 class="mb-4">Car Catalog</h2>
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($car = mysqli_fetch_assoc($result)): ?>
                <div class="col">
                    <div class="card car-card h-100">
                        <div class="position-relative">
                            <img src="../assets/images/cars/<?php echo htmlspecialchars($car['image']); ?>" 
                                 class="card-img-top car-image" 
                                 alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>"
                                 onerror="this.src='../assets/images/car-placeholder.jpg'">
                            
                            <span class="status-badge <?php echo $car['availability_status'] === 'available' ? 'status-available' : 'status-rented'; ?>">
                                <?php echo ucfirst($car['availability_status']); ?>
                            </span>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                            <div class="specs mb-3">
                                <div><i class="fas fa-calendar"></i> Year: <?php echo htmlspecialchars($car['year']); ?></div>
                                <div><i class="fas fa-cog"></i> Transmission: <?php echo ucfirst($car['transmission']); ?></div>
                                <div><i class="fas fa-gas-pump"></i> Fuel: <?php echo htmlspecialchars($car['fuel_type']); ?></div>
                                <div><i class="fas fa-users"></i> Seats: <?php echo htmlspecialchars($car['seats']); ?></div>
                                <div><i class="fas fa-palette"></i> Color: <?php echo htmlspecialchars($car['color']); ?></div>
                            </div>

                            <p class="card-text">
                                <small class="text-muted"><?php echo htmlspecialchars($car['description']); ?></small>
                            </p>

                            <h5 class="mb-3">Rp <?php echo number_format($car['daily_rate'], 0, ',', '.'); ?> / day</h5>

                            <?php if ($car['availability_status'] === 'available'): ?>
                                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" 
                                        data-bs-target="#rentModal<?php echo $car['id']; ?>">
                                    <i class="fas fa-car"></i> Rent Now
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-ban"></i> Currently Rented
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Rent Modal -->
                <?php if ($car['availability_status'] === 'available'): ?>
                <div class="modal fade" id="rentModal<?php echo $car['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Rent <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="process_rental.php" method="POST">
                                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                        <input type="hidden" name="daily_rate" value="<?php echo $car['daily_rate']; ?>">
                                        <input type="hidden" name="rent_car" value="1">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" class="form-control" name="start_date" required 
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">End Date</label>
                                            <input type="date" class="form-control" name="end_date" required 
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Daily Rate</label>
                                            <div class="form-control">Rp <?php echo number_format($car['daily_rate'], 0, ',', '.'); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Total Amount</label>
                                            <div class="form-control total-amount">Rp 0</div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Confirm Rental</button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        Please <a href="login.php">login</a> or <a href="register.php">register</a> to rent this car.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('input[type="date"]').on('change', function() {
                var form = $(this).closest('form');
                var startDate = new Date(form.find('input[name="start_date"]').val());
                var endDate = new Date(form.find('input[name="end_date"]').val());
                var dailyRate = parseFloat(form.find('input[name="daily_rate"]').val());

                if (startDate && endDate && startDate <= endDate) {
                    var days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                    var total = days * dailyRate;
                    form.find('.total-amount').text('Rp ' + total.toLocaleString('id-ID'));
                }
            });
        });
    </script>
</body>
</html>
