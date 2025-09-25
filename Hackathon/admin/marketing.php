<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') { header("Location: ../index.php"); exit; }

// Top customer addresses
$addresses = $conn->query("SELECT address, COUNT(*) as count FROM customer_addresses GROUP BY address ORDER BY count DESC LIMIT 10");
?>
<h2>Top Customer Addresses</h2>
<table border="1">
<tr><th>Address</th><th>Count</th></tr>
<?php while($row=$addresses->fetch_assoc()): ?>
<tr>
    <td><?= $row['address'] ?></td>
    <td><?= $row['count'] ?></td>
</tr>
<?php endwhile; ?>
</table>
