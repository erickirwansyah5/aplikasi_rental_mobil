<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rent_car'])) {
 
    $user_id = $_SESSION['user_id'];
    $car_id = (int)$_POST['car_id'];
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $daily_rate = (float)$_POST['daily_rate'];

    // Validate dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = new DateTime();
    
    if ($start <= $today) {
        $_SESSION['error_message'] = "Rent Must Be H-1 Day";
        header("Location: catalog.php");
        exit();
    }
    
    if ($end < $start) {
        $_SESSION['error_message'] = "End date must be after start date";
        header("Location: catalog.php");
        exit();
    }

    // Calculate total days (including both start and end days)
    $days = $end->diff($start)->days + 1;

    // Calculate total amount
    $total_amount = $days * $daily_rate;

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Check if car is available (not currently rented)
        $query = "SELECT COUNT(*) as active_rentals 
                 FROM rentals 
                 WHERE car_id = ? 
                 AND status IN ('active', 'pending')
                 AND (
                     (start_date <= ? AND end_date >= ?) OR
                     (start_date <= ? AND end_date >= ?) OR
                     (start_date >= ? AND end_date <= ?)
                 )";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issssss", 
            $car_id, 
            $end_date, $start_date,  // Check if existing rental overlaps with start
            $start_date, $start_date, // Check if existing rental overlaps with end
            $start_date, $end_date    // Check if existing rental is within new rental
        );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['active_rentals'] > 0) {
            throw new Exception("Car is not available for the selected dates");
        }

        // Insert rental
        $query = "INSERT INTO rentals (customer_id, car_id, start_date, end_date, total_amount, status) 
                 VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iissd", $user_id, $car_id, $start_date, $end_date, $total_amount);
        mysqli_stmt_execute($stmt);
        
        $rental_id = mysqli_insert_id($conn);

        // Create initial payment record
        $query = "INSERT INTO payments (rental_id, amount, payment_status) 
                 VALUES (?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "id", $rental_id, $total_amount);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
        $_SESSION['success_message'] = "Rental request submitted successfully! Please proceed with payment confirmation.";
        header("Location: confirm_payment.php?rental_id=" . $rental_id);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Failed to process rental: " . $e->getMessage();
        header("Location: catalog.php");
        exit();
    }
}

header("Location: catalog.php");
exit();
