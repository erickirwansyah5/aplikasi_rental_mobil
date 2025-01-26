<?php
/**
 * CarRent - Car Rental Management System
 * Car Management Module
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
    if (isset($_POST['add_car'])) {
        $brand = mysqli_real_escape_string($conn, $_POST['brand']);
        $model = mysqli_real_escape_string($conn, $_POST['model']);
        $year = (int)$_POST['year'];
        $license_plate = mysqli_real_escape_string($conn, $_POST['license_plate']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $daily_rate = (float)$_POST['daily_rate'];
        // $image = mysqli_real_escape_string($conn, $_POST['image']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        // Check if image is uploaded and set the path
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/cars/' . $image);
        }

        $query = "INSERT INTO cars (brand, model, year, license_plate, color, daily_rate, image, status) 
                 VALUES ('$brand', '$model', $year, '$license_plate', '$color', $daily_rate, '$image', '$status')";
        mysqli_query($conn, $query);
    }

    if (isset($_POST['delete_car'])) {
        $car_id = (int)$_POST['car_id'];
        $query = "DELETE FROM cars WHERE id = $car_id";
        mysqli_query($conn, $query);
    }

    if (isset($_POST['update_car'])) {
        $car_id = (int)$_POST['car_id'];
        $brand = mysqli_real_escape_string($conn, $_POST['brand']);
        $model = mysqli_real_escape_string($conn, $_POST['model']);
        $year = (int)$_POST['year'];
        $license_plate = mysqli_real_escape_string($conn, $_POST['license_plate']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $daily_rate = (float)$_POST['daily_rate'];
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // jika image diupload, set path baru, jika tidak, tetapkan path yang ada
        if($_FILES['image']['name'] !== '') {
            $image = $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/cars/' . $image);
        } else {
            $image = mysqli_real_escape_string($conn, $_FILES['image']['name']);
        }

        $query = "UPDATE cars SET 
                 brand = '$brand',
                 model = '$model',
                 year = $year,
                 license_plate = '$license_plate',
                 color = '$color',
                 daily_rate = $daily_rate,
                 status = '$status',
                 image = '$image'
                 WHERE id = $car_id";
        mysqli_query($conn, $query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRent - Cars Management</title>
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
                    <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="cars.php"><i class="fas fa-car"></i> Cars</a>
                </li>
                <li>
                    <a href="rentals.php"><i class="fas fa-receipt"></i> Rentals</a>
                </li>
                <li>
                    <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li>
                    <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
                <li>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                    <h2>Cars Management</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCarModal">
                        <i class="fas fa-plus"></i> Add New Car
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Brand</th>
                                        <th>Model</th>
                                        <th>Year</th>
                                        <th>License Plate</th>
                                        <th>Color</th>
                                        <th>Daily Rate</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM cars ORDER BY brand, model";
                                    $result = mysqli_query($conn, $query);
                                    while ($car = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td><?php echo $car['brand']; ?></td>
                                        <td><?php echo $car['model']; ?></td>
                                        <td><?php echo $car['year']; ?></td>
                                        <td><?php echo $car['license_plate']; ?></td>
                                        <td><?php echo $car['color']; ?></td>
                                        <td>Rp <?php echo number_format($car['daily_rate']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $car['status'] == 'available' ? 'success' : 
                                                    ($car['status'] == 'rented' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo $car['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#editCarModal<?php echo $car['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#deleteCarModal<?php echo $car['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Car Modal -->
                                    <div class="modal fade" id="editCarModal<?php echo $car['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Car</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Image</label>
                                                            <input type="file" class="form-control" name="image" value="<?php echo $car['image']; ?>" >
                                                            <img src="../assets/images/cars/<?php echo $car['image']; ?>" width="100" height="100" alt="<?php echo $car['brand']; ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Brand</label>
                                                            <input type="text" class="form-control" name="brand" value="<?php echo $car['brand']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Model</label>
                                                            <input type="text" class="form-control" name="model" value="<?php echo $car['model']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Year</label>
                                                            <input type="number" class="form-control" name="year" value="<?php echo $car['year']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">License Plate</label>
                                                            <input type="text" class="form-control" name="license_plate" value="<?php echo $car['license_plate']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Color</label>
                                                            <input type="text" class="form-control" name="color" value="<?php echo $car['color']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Daily Rate</label>
                                                            <input type="number" class="form-control" name="daily_rate" value="<?php echo $car['daily_rate']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select class="form-select" name="status" required>
                                                                <option value="available" <?php echo $car['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                                                <option value="rented" <?php echo $car['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                                                                <option value="maintenance" <?php echo $car['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_car" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Car Modal -->
                                    <div class="modal fade" id="deleteCarModal<?php echo $car['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Delete Car</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this car?</p>
                                                    <p><strong><?php echo $car['brand'] . ' ' . $car['model']; ?></strong></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form method="POST">
                                                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="delete_car" class="btn btn-danger">Delete</button>
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

    <!-- Add Car Modal -->
    <div class="modal fade" id="addCarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                    <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" class="form-control" name="year" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">License Plate</label>
                            <input type="text" class="form-control" name="license_plate" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Daily Rate</label>
                            <input type="number" class="form-control" name="daily_rate" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select Status</option>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_car" class="btn btn-primary">Add Car</button>
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
