<?php
/**
 * CarRent - Car Rental Management System
 * Reports Module
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

// Get date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get total revenue
$revenue_query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
                 FROM rentals 
                 WHERE created_at BETWEEN ? AND ?
                 AND status != 'cancelled'";
$stmt = mysqli_prepare($conn, $revenue_query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$revenue_result = mysqli_stmt_get_result($stmt);
$revenue_data = mysqli_fetch_assoc($revenue_result);
$total_revenue = $revenue_data['total_revenue'];

// Get active rentals count
$active_rentals_query = "SELECT COUNT(*) as active_count 
                        FROM rentals 
                        WHERE status = 'active'
                        AND created_at BETWEEN ? AND ?";
$stmt = mysqli_prepare($conn, $active_rentals_query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$active_result = mysqli_stmt_get_result($stmt);
$active_data = mysqli_fetch_assoc($active_result);
$active_rentals = $active_data['active_count'];

// Get completed rentals count
$completed_rentals_query = "SELECT COUNT(*) as completed_count 
                           FROM rentals 
                           WHERE status = 'completed'
                           AND created_at BETWEEN ? AND ?";
$stmt = mysqli_prepare($conn, $completed_rentals_query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$completed_result = mysqli_stmt_get_result($stmt);
$completed_data = mysqli_fetch_assoc($completed_result);
$completed_rentals = $completed_data['completed_count'];

// Get most popular cars
$popular_cars_query = "SELECT c.brand, c.model, COUNT(r.id) as rental_count 
                      FROM rentals r
                      JOIN cars c ON r.car_id = c.id
                      WHERE r.created_at BETWEEN ? AND ?
                      GROUP BY c.id
                      ORDER BY rental_count DESC
                      LIMIT 5";
$stmt = mysqli_prepare($conn, $popular_cars_query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$popular_cars_result = mysqli_stmt_get_result($stmt);

// Get rental status distribution
$status_query = "SELECT status, COUNT(*) as count 
                FROM rentals 
                WHERE created_at BETWEEN ? AND ?
                GROUP BY status";
$stmt = mysqli_prepare($conn, $status_query);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$status_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - CarRent Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .wrapper {
            display: flex;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #2c3136;
        }
        #sidebar ul.components {
            padding: 20px 0;
        }
        #sidebar ul li a {
            padding: 10px 20px;
            font-size: 1.1em;
            display: block;
            color: #fff;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            background: #2c3136;
        }
        #sidebar ul li.active > a {
            background: #2c3136;
        }
        #content {
            width: 100%;
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>CarRent Admin</h3>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li>
                    <a href="cars.php"><i class="fas fa-car"></i> Cars</a>
                </li>
                <li>
                    <a href="rentals.php"><i class="fas fa-receipt"></i> Rentals</a>
                </li>
                <li>
                    <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li class="active">
                    <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
                <li>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Date Range Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="start_date" class="col-form-label">Start Date</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-auto">
                            <label for="end_date" class="col-form-label">End Date</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card bg-primary text-white">
                        <h5>Total Revenue</h5>
                        <h2>Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-success text-white">
                        <h5>Active Rentals</h5>
                        <h2><?php echo $active_rentals; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-info text-white">
                        <h5>Completed Rentals</h5>
                        <h2><?php echo $completed_rentals; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Most Popular Cars -->
                <div class="col-md-6">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Most Popular Cars</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Car</th>
                                            <th>Total Rentals</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($car = mysqli_fetch_assoc($popular_cars_result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></td>
                                            <td><?php echo $car['rental_count']; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rental Status Distribution -->
                <div class="col-md-6">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Rental Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($status = mysqli_fetch_assoc($status_result)): ?>
                                        <tr>
                                            <td><?php echo ucfirst($status['status']); ?></td>
                                            <td><?php echo $status['count']; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>
