<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch current queue
$queue = $conn->query("SELECT 
                            q.id AS queue_id,
                            c.name AS customer_name,
                            b.name AS barber_name,
                            q.status,
                            q.created_at
                        FROM queue q
                        LEFT JOIN users c ON q.customer_id = c.id
                        LEFT JOIN users b ON q.barber_id = b.id
                        ORDER BY q.created_at ASC;
                        ");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Queue Status</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h2>Current Queue</h2>
<table border="1">
    <tr>
        <th>Customer</th>
        <th>Barber</th>
        <th>Queue Number</th>
    </tr>
    <?php while($row = $queue->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['customer_name']); ?></td>
        <td><?= htmlspecialchars($row['barber_name']); ?></td>
        <td><?= $row['queue_id']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
