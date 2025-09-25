<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'barber') {
    header("Location: ../index.php"); exit;
}
$barber_id = $_SESSION['user']['id'];

// Fetch queue assigned to this barber OR without any barber assigned
$queue = $conn->query("
    SELECT q.id AS queue_number, 
           COALESCE(u.name, 'Walk-in') AS customer_name, 
           q.status, 
           q.created_at, 
           q.barber_id
    FROM queue q 
    LEFT JOIN users u ON q.customer_id = u.id
    WHERE (q.barber_id = $barber_id OR q.barber_id IS NULL) 
      AND q.status != 'finished'
    ORDER BY q.id ASC
");
?>
<h2>Barber Dashboard</h2>
<p><a href="../logout.php">Logout</a></p>
<p><a href="performance.php">Check Performance</a></p>
<table border="1">
    <tr>
        <th>Queue Number</th>
        <th>Customer</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while($row=$queue->fetch_assoc()): ?>
    <tr>
        <td><?= $row['queue_number'] ?></td>
        <td><?= $row['customer_name'] ?></td>
        <td><?= $row['status'] ?></td>
        <td>
            <?php if($row['status']=='waiting'): ?>
                <a href="start_service.php?id=<?= $row['queue_number'] ?>">Start</a>
            <?php elseif($row['status']=='in_service'): ?>
                <a href="finish_service.php?id=<?= $row['queue_number'] ?>">Finish</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
