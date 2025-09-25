<?php
session_start();
include '../config/db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'barber') {
    header("Location: ../index.php");
    exit;
}

$barber_id = $_SESSION['user']['id'];

// Fetch performance data
$performance = $conn->query("
    SELECT customers_served, avg_service_time, month 
    FROM performance 
    WHERE barber_id=$barber_id 
    ORDER BY month DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Performance</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <h2>My Performance</h2>

    <?php if($performance->num_rows > 0): ?>
    <table border="1">
        <tr>
            <th>Month</th>
            <th>Customers Served</th>
            <th>Average Service Time (min)</th>
        </tr>
        <?php while($row = $performance->fetch_assoc()): ?>
        <tr>
            <td><?= $row['month'] ?></td>
            <td><?= $row['customers_served'] ?></td>
            <td><?= round($row['avg_service_time'],2) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No performance data available yet.</p>
    <?php endif; ?>

    <p><a href="dashboard.php">Back to Dashboard</a> | <a href="../logout.php">Logout</a></p>
</div>
</body>
</html>
