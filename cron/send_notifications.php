<?php
include '../config/database.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to send email
function sendEmail($to, $subject, $body) {
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
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Process pending email notifications
$query = "SELECT n.*, u.email, r.start_date, r.end_date, c.brand, c.model, 
          pl.name as pickup_location, pl.address as pickup_address
          FROM email_notifications n
          JOIN users u ON n.user_id = u.id
          JOIN rentals r ON n.rental_id = r.id
          JOIN cars c ON r.car_id = c.id
          LEFT JOIN pickup_locations pl ON r.pickup_location_id = pl.id
          WHERE n.email_status = 'pending'
          ORDER BY n.created_at ASC
          LIMIT 50";

$result = mysqli_query($conn, $query);

while ($notification = mysqli_fetch_assoc($result)) {
    $email_sent = false;
    $email_body = "";
    $subject = "";

    switch ($notification['notification_type']) {
        case 'rental_confirmation':
            $subject = "Rental Confirmation - CarRent";
            $email_body = "
                <h2>Rental Confirmation</h2>
                <p>Your rental request has been received:</p>
                <ul>
                    <li>Car: {$notification['brand']} {$notification['model']}</li>
                    <li>Start Date: {$notification['start_date']}</li>
                    <li>End Date: {$notification['end_date']}</li>
                </ul>
                <p>Please complete the payment to proceed with your rental.</p>
            ";
            break;

        case 'payment_confirmation':
            $subject = "Payment Received - CarRent";
            $email_body = "
                <h2>Payment Received</h2>
                <p>We have received your payment for:</p>
                <ul>
                    <li>Car: {$notification['brand']} {$notification['model']}</li>
                    <li>Start Date: {$notification['start_date']}</li>
                    <li>End Date: {$notification['end_date']}</li>
                </ul>
                <p>Our admin will review your payment shortly.</p>
            ";
            break;

        case 'admin_approval':
            $subject = "Rental Status Update - CarRent";
            $email_body = "
                <h2>Rental Approved</h2>
                <p>Your rental has been approved:</p>
                <ul>
                    <li>Car: {$notification['brand']} {$notification['model']}</li>
                    <li>Start Date: {$notification['start_date']}</li>
                    <li>End Date: {$notification['end_date']}</li>
                    <li>Pickup Location: {$notification['pickup_location']}</li>
                    <li>Address: {$notification['pickup_address']}</li>
                </ul>
                <p>Please arrive at the pickup location on the scheduled date with your ID.</p>
            ";
            break;

        case 'pickup_reminder':
            $subject = "Pickup Reminder - CarRent";
            $email_body = "
                <h2>Pickup Reminder</h2>
                <p>This is a reminder for your upcoming car rental:</p>
                <ul>
                    <li>Car: {$notification['brand']} {$notification['model']}</li>
                    <li>Pickup Date: {$notification['start_date']}</li>
                    <li>Return Date: {$notification['end_date']}</li>
                    <li>Pickup Location: {$notification['pickup_location']}</li>
                    <li>Address: {$notification['pickup_address']}</li>
                </ul>
                <p>Please don't forget to bring your ID and rental confirmation.</p>
            ";
            break;
    }

    if ($email_body && $subject) {
        $email_sent = sendEmail($notification['email'], $subject, $email_body);
    }

    // Update notification status
    $status = $email_sent ? 'sent' : 'failed';
    $query = "UPDATE email_notifications 
              SET email_status = ?, sent_at = NOW() 
              WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $notification['id']);
    mysqli_stmt_execute($stmt);
}

// Send pickup reminders for tomorrow's rentals
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$query = "SELECT r.*, u.email, u.id as user_id, c.brand, c.model,
          pl.name as pickup_location, pl.address as pickup_address
          FROM rentals r
          JOIN users u ON r.customer_id = u.id
          JOIN cars c ON r.car_id = c.id
          JOIN pickup_locations pl ON r.pickup_location_id = pl.id
          WHERE DATE(r.start_date) = ? 
          AND r.status = 'active'
          AND r.admin_approval_status = 'approved'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $tomorrow);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($rental = mysqli_fetch_assoc($result)) {
    // Check if reminder already sent
    $query = "SELECT id FROM email_notifications 
              WHERE rental_id = ? AND notification_type = 'pickup_reminder'";
    $check_stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($check_stmt, "i", $rental['id']);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) == 0) {
        // Create pickup reminder notification
        $query = "INSERT INTO email_notifications 
                 (user_id, rental_id, notification_type) 
                 VALUES (?, ?, 'pickup_reminder')";
        $insert_stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($insert_stmt, "ii", $rental['user_id'], $rental['id']);
        mysqli_stmt_execute($insert_stmt);
    }
}
?>
