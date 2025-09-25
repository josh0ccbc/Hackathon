<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') { header("Location: ../index.php"); exit; }

$customer_id = $_SESSION['user']['id'];
$appointments = $conn->query("SELECT a.*, u.name AS barber_name FROM appointments a LEFT JOIN users u ON a.barber_id=u.id WHERE a.customer_id=$customer_id ORDER BY a.appointment_time DESC");
?>
<h2>Appointment History</h2>
<table border="1">
<tr><th>Barber</th><th>Time</th><th>Status</th></tr>
<?php while($row=$appointments->fetch_assoc()): ?>
<tr>
    <td><?= $row['barber_name'] ?: "Any" ?></td>
    <td><?= $row['appointment_time'] ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>
</table>
