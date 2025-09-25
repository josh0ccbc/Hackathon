<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') { header("Location: ../index.php"); exit; }

$performance = $conn->query("SELECT p.*, u.name AS barber_name FROM performance p JOIN users u ON p.barber_id=u.id ORDER BY p.customers_served DESC");
?>
<h2>Employee Performance</h2>
<table border="1">
<tr><th>Barber</th><th>Customers Served</th><th>Average Service Time (min)</th><th>Month</th></tr>
<?php while($row=$performance->fetch_assoc()): ?>
<tr>
    <td><?= $row['barber_name'] ?></td>
    <td><?= $row['customers_served'] ?></td>
    <td><?= round($row['avg_service_time'],2) ?></td>
    <td><?= $row['month'] ?></td>
</tr>
<?php endwhile; ?>
</table>
