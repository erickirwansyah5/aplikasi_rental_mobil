<?php
include '../config/database.php';

// Update car stock for completed rentals
$today = date('Y-m-d');

// Get rentals that ended yesterday and are still active
$query = "SELECT r.*, c.stock 
          FROM rentals r
          JOIN cars c ON r.car_id = c.id
          WHERE r.status = 'active' 
          AND r.end_date < ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($rental = mysqli_fetch_assoc($result)) {
    mysqli_begin_transaction($conn);
    
    try {
        // Update rental status to completed
        $query = "UPDATE rentals 
                 SET status = 'completed' 
                 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $rental['id']);
        mysqli_stmt_execute($stmt);

        // Increase car stock
        $query = "UPDATE cars 
                 SET stock = stock + 1 
                 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $rental['car_id']);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error updating rental {$rental['id']}: " . $e->getMessage());
    }
}

// Check for overdue rentals
$query = "SELECT r.*, u.email 
          FROM rentals r
          JOIN users u ON r.customer_id = u.id
          WHERE r.status = 'active' 
          AND r.end_date < ?
          AND r.id NOT IN (
              SELECT rental_id 
              FROM email_notifications 
              WHERE notification_type = 'overdue'
          )";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($rental = mysqli_fetch_assoc($result)) {
    // Create overdue notification
    $query = "INSERT INTO email_notifications 
             (user_id, rental_id, notification_type) 
             VALUES (?, ?, 'overdue')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $rental['customer_id'], $rental['id']);
    mysqli_stmt_execute($stmt);
}
?>
