<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: ../index.php"); 
    exit;
}

$customer_id = $_SESSION['user']['id'];

// Fetch current queue info
$queue = $conn->query("
    SELECT q.id, u.name AS barber_name, q.status, q.created_at 
    FROM queue q
    LEFT JOIN users u ON q.barber_id = u.id
    WHERE q.customer_id = $customer_id AND q.status != 'finished'
    ORDER BY q.created_at ASC
");

// Fetch upcoming appointments
$appointments = $conn->query("
    SELECT a.*, u.name AS barber_name 
    FROM appointments a 
    LEFT JOIN users u ON a.barber_id = u.id 
    WHERE a.customer_id = $customer_id AND a.status='pending'
    ORDER BY a.appointment_time ASC
");

// Fetch reward points
$reward = $conn->query("SELECT points, referral_count FROM rewards WHERE customer_id=$customer_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?></h2>

    <!-- Rewards -->
    <h3>Rewards & Loyalty</h3>
    <p>Points: <?= $reward['points'] ?? 0 ?> | Referrals: <?= $reward['referral_count'] ?? 0 ?></p>

    <!-- Current Queue -->
    <h3>Current Queue</h3>
    <?php if($queue->num_rows > 0): ?>
    <table border="1">
        <tr>
            <th>Queue Number</th>
            <th>Barber</th>
            <th>Status</th>
            <th>Booked At</th>
        </tr>
        <?php while($q = $queue->fetch_assoc()): ?>
        <tr>
            <td><?= $q['id'] ?></td>
            <td><?= $q['barber_name'] ?: 'Any' ?></td>
            <td><?= $q['status'] ?></td>
            <td><?= $q['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>You are not in any queue currently.</p>
    <?php endif; ?>

    <!-- Upcoming Appointments -->
    <h3>Upcoming Appointments</h3>
    <?php if($appointments->num_rows > 0): ?>
    <table border="1">
        <tr>
            <th>Appointment ID</th>
            <th>Barber</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
        <?php while($a = $appointments->fetch_assoc()): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= $a['barber_name'] ?: 'Any' ?></td>
            <td><?= $a['appointment_time'] ?></td>
            <td><?= $a['status'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No upcoming appointments.</p>
    <?php endif; ?>

    <!-- Quick Links -->
    <p><a href="appointment.php">Book Appointment</a> | <a href="queue.php">View Queue</a> | <a href="../logout.php">Logout</a></p>
</div>
</body>
</html>
