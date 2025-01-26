<?php
/**
 * CarRent - Car Rental Management System
 * Rental Management Module
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
include '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_rental'])) {
        $customer_id = (int)$_POST['customer_id'];
        $car_id = (int)$_POST['car_id'];
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        
        // Calculate total days
        $start = new DateTime($_POST['start_date']);
        $end = new DateTime($_POST['end_date']);
        $days = $end->diff($start)->days;

        // Get car daily rate
        $query = "SELECT daily_rate FROM cars WHERE id = $car_id";
        $result = mysqli_query($conn, $query);
        $car = mysqli_fetch_assoc($result);
        $total_amount = $days * $car['daily_rate'];

        // Insert rental
        $query = "INSERT INTO rentals (customer_id, car_id, start_date, end_date, total_amount) 
                 VALUES ($customer_id, $car_id, '$start_date', '$end_date', $total_amount)";
        mysqli_query($conn, $query);

        // Update car status
        $query = "UPDATE cars SET status = 'rented' WHERE id = $car_id";
        mysqli_query($conn, $query);
    }

    if (isset($_POST['complete_rental'])) {
        $rental_id = (int)$_POST['rental_id'];
        $car_id = (int)$_POST['car_id'];

        // Update rental status
        $query = "UPDATE rentals SET status = 'completed' WHERE id = $rental_id";
        mysqli_query($conn, $query);

        // Update car status
        $query = "UPDATE cars SET status = 'available' WHERE id = $car_id";
        mysqli_query($conn, $query);
    }

    if (isset($_POST['cancel_rental'])) {
        $rental_id = (int)$_POST['rental_id'];
        $car_id = (int)$_POST['car_id'];

        // Update rental status
        $query = "UPDATE rentals SET status = 'cancelled' WHERE id = $rental_id";
        mysqli_query($conn, $query);

        // Update car status
        $query = "UPDATE cars SET status = 'available' WHERE id = $car_id";
        mysqli_query($conn, $query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRent - Rentals Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="active">
            <div class="sidebar-header">
                <h3>CarRent</h3>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li>
                    <a href="../cars.php"><i class="fas fa-car"></i> Cars</a>
                </li>
                <li class="active">
                    <a href="../rentals.php"><i class="fas fa-receipt"></i> Rentals</a>
                </li>
                <li>
                    <a href="../customers.php"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li>
                    <a href="../reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
                <li>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Rentals Management</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRentalModal">
                        <i class="fas fa-plus"></i> Add New Rental
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT r.*, c.name as customer_name, ca.brand, ca.model 
                                             FROM rentals r 
                                             JOIN customers c ON r.customer_id = c.id 
                                             JOIN cars ca ON r.car_id = ca.id 
                                             ORDER BY r.created_at DESC";
                                    $result = mysqli_query($conn, $query);
                                    while ($rental = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td><?php echo $rental['customer_name']; ?></td>
                                        <td><?php echo $rental['brand'] . ' ' . $rental['model']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rental['start_date'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rental['end_date'])); ?></td>
                                        <td>Rp <?php echo number_format($rental['total_amount']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $rental['status'] == 'active' ? 'success' : 
                                                    ($rental['status'] == 'completed' ? 'primary' : 'danger'); 
                                            ?>">
                                                <?php echo $rental['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($rental['status'] == 'active'): ?>
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" 
                                                        data-bs-target="#completeRentalModal<?php echo $rental['id']; ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                        data-bs-target="#cancelRentalModal<?php echo $rental['id']; ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Complete Rental Modal -->
                                    <div class="modal fade" id="completeRentalModal<?php echo $rental['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Complete Rental</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to complete this rental?</p>
                                                    <p><strong>Customer:</strong> <?php echo $rental['customer_name']; ?></p>
                                                    <p><strong>Car:</strong> <?php echo $rental['brand'] . ' ' . $rental['model']; ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST">
                                                        <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                                        <input type="hidden" name="car_id" value="<?php echo $rental['car_id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="complete_rental" class="btn btn-success">Complete Rental</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cancel Rental Modal -->
                                    <div class="modal fade" id="cancelRentalModal<?php echo $rental['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Cancel Rental</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to cancel this rental?</p>
                                                    <p><strong>Customer:</strong> <?php echo $rental['customer_name']; ?></p>
                                                    <p><strong>Car:</strong> <?php echo $rental['brand'] . ' ' . $rental['model']; ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST">
                                                        <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                                        <input type="hidden" name="car_id" value="<?php echo $rental['car_id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="cancel_rental" class="btn btn-danger">Cancel Rental</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Rental Modal -->
    <div class="modal fade" id="addRentalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Rental</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php
                                $query = "SELECT * FROM customers ORDER BY name";
                                $result = mysqli_query($conn, $query);
                                while ($customer = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$customer['id']}'>{$customer['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Car</label>
                            <select class="form-select" name="car_id" required>
                                <option value="">Select Car</option>
                                <?php
                                $query = "SELECT * FROM cars WHERE status = 'available' ORDER BY brand, model";
                                $result = mysqli_query($conn, $query);
                                while ($car = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$car['id']}'>{$car['brand']} {$car['model']} - {$car['license_plate']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_rental" class="btn btn-primary">Add Rental</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>
