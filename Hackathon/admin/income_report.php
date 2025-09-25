<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') { header("Location: ../index.php"); exit; }

$period = $_GET['period'] ?? 'daily';
if($period=='weekly'){
    $payments = $conn->query("SELECT SUM(amount) as total, DATE(paid_at) as date FROM payments WHERE YEARWEEK(paid_at)=YEARWEEK(CURDATE()) GROUP BY DATE(paid_at)");
} elseif($period=='monthly'){
    $payments = $conn->query("SELECT SUM(amount) as total, DATE(paid_at) as date FROM payments WHERE MONTH(paid_at)=MONTH(CURDATE()) GROUP BY DATE(paid_at)");
} else {
    $payments = $conn->query("SELECT SUM(amount) as total, DATE(paid_at) as date FROM payments WHERE DATE(paid_at)=CURDATE() GROUP BY DATE(paid_at)");
}
?>
<h2>Income Report (<?= ucfirst($period) ?>)</h2>
<table border="1">
<tr><th>Date</th><th>Total Income</th></tr>
<?php while($row=$payments->fetch_assoc()): ?>
<tr>
    <td><?= $row['date'] ?></td>
    <td>₱<?= $row['total'] ?></td>
</tr>
<?php endwhile; ?>
</table>
