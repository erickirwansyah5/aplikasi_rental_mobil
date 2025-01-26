<?php
session_start();
include '../config/database.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rental_id = (int)$_POST['rental_id'];
    $action = $_POST['action'];
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);

    mysqli_begin_transaction($conn);

    try {
        // Get rental and payment information
        $query = "SELECT r.*, c.stock, c.brand, c.model, p.payment_status, u.email 
                  FROM rentals r 
                  JOIN cars c ON r.car_id = c.id 
                  JOIN payments p ON r.id = p.rental_id 
                  JOIN users u ON r.customer_id = u.id 
                  WHERE r.id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $rental_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rental = mysqli_fetch_assoc($result);

        if ($action === 'approve') {
            // Check if car is still available
            if ($rental['stock'] < 1) {
                throw new Exception("Car is no longer available");
            }

            // Update rental status
            $query = "UPDATE rentals SET 
                     admin_approval_status = 'approved',
                     admin_approval_date = NOW(),
                     admin_notes = ?,
                     status = 'active'
                     WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $admin_notes, $rental_id);
            mysqli_stmt_execute($stmt);

            // Update car stock
            $query = "UPDATE cars SET stock = stock - 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $rental['car_id']);
            mysqli_stmt_execute($stmt);

            // Update payment status
            $query = "UPDATE payments SET payment_status = 'paid' WHERE rental_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $rental_id);
            mysqli_stmt_execute($stmt);

        } else {
            // Reject rental
            $query = "UPDATE rentals SET 
                     admin_approval_status = 'rejected',
                     admin_approval_date = NOW(),
                     admin_notes = ?,
                     status = 'cancelled'
                     WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $admin_notes, $rental_id);
            mysqli_stmt_execute($stmt);

            // Update payment status
            $query = "UPDATE payments SET payment_status = 'failed' WHERE rental_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $rental_id);
            mysqli_stmt_execute($stmt);
        }

        // Create email notification
        $query = "INSERT INTO email_notifications (user_id, rental_id, notification_type) 
                 VALUES (?, ?, 'admin_approval')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $rental['customer_id'], $rental_id);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);

        // Send email notification
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Replace with your email
            $mail->Password = 'your-password'; // Replace with your password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'CarRent System');
            $mail->addAddress($rental['email']);

            // Content
            $mail->isHTML(true);
            $status_text = $action === 'approve' ? 'Approved' : 'Rejected';
            $mail->Subject = "Rental {$status_text} - CarRent";
            $mail->Body = "
                <h2>Rental {$status_text}</h2>
                <p>Your rental has been {$status_text} by our admin.</p>
                <h3>Rental Details:</h3>
                <ul>
                    <li>Car: {$rental['brand']} {$rental['model']}</li>
                    <li>Start Date: {$rental['start_date']}</li>
                    <li>End Date: {$rental['end_date']}</li>
                </ul>
                " . ($admin_notes ? "<p><strong>Admin Notes:</strong> {$admin_notes}</p>" : "") . "
                " . ($action === 'approve' ? "<p>Please pick up your car at the selected location on the scheduled date.</p>" : "");

            $mail->send();
        } catch (Exception $e) {
            // Log email error but don't show to user
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        $success_message = "Rental has been " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully!";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = $e->getMessage();
    }
}

// Get pending rentals
$query = "SELECT r.*, c.brand, c.model, c.stock, u.email, u.username,
          p.payment_method, p.proof_of_payment, pl.name as pickup_location, pl.address as pickup_address
          FROM rentals r 
          JOIN cars c ON r.car_id = c.id 
          JOIN users u ON r.customer_id = u.id 
          JOIN payments p ON r.id = p.rental_id 
          JOIN pickup_locations pl ON r.pickup_location_id = pl.id 
          WHERE r.admin_approval_status = 'pending' 
          ORDER BY r.created_at DESC";
$pending_rentals = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Rentals - CarRent Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .proof-image {
            max-width: 200px;
            cursor: pointer;
        }
        .modal-image {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

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

        <h2>Pending Rental Approvals</h2>

        <?php if (mysqli_num_rows($pending_rentals) > 0): ?>
            <?php while ($rental = mysqli_fetch_assoc($pending_rentals)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Rental #<?php echo $rental['id']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($rental['username']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($rental['email']); ?></p>

                                <h6 class="mt-4">Rental Details</h6>
                                <p><strong>Car:</strong> <?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?></p>
                                <p><strong>Current Stock:</strong> <?php echo $rental['stock']; ?></p>
                                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($rental['start_date']); ?></p>
                                <p><strong>End Date:</strong> <?php echo htmlspecialchars($rental['end_date']); ?></p>
                                <p><strong>Total Price:</strong> Rp <?php echo number_format($rental['total_price'], 0, ',', '.'); ?></p>
                            </div>

                            <div class="col-md-6">
                                <h6>Pickup Location</h6>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($rental['pickup_location']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($rental['pickup_address']); ?></p>

                                <h6 class="mt-4">Payment Information</h6>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $rental['payment_method'])); ?></p>
                                <p><strong>Proof of Payment:</strong></p>
                                <img src="../uploads/payments/<?php echo $rental['proof_of_payment']; ?>" 
                                     class="proof-image" 
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal<?php echo $rental['id']; ?>"
                                     alt="Proof of Payment">
                            </div>
                        </div>

                        <form method="POST" class="mt-4">
                            <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea class="form-control" name="admin_notes" rows="3"></textarea>
                            </div>
                            <button type="submit" name="action" value="approve" class="btn btn-success" 
                                    <?php echo $rental['stock'] < 1 ? 'disabled' : ''; ?>>
                                Approve Rental
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                Reject Rental
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Image Modal -->
                <div class="modal fade" id="imageModal<?php echo $rental['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Proof of Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="../uploads/payments/<?php echo $rental['proof_of_payment']; ?>" 
                                     class="modal-image" 
                                     alt="Proof of Payment">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                No pending rentals to approve.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
