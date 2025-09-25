<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') { header("Location: ../index.php"); exit; }

// Customers today
date_default_timezone_set('Asia/Manila'); // PHT
$today = date('Y-m-d', strtotime('-1 day'));
$customers = $conn->query("SELECT COUNT(*) as total FROM queue WHERE DATE(created_at)='$today'")->fetch_assoc()['total'];

// Total income today
$income = $conn->query("SELECT SUM(amount) as total_income FROM payments WHERE DATE(paid_at)='$today'")->fetch_assoc()['total_income'];
?>
<h2>Admin Dashboard</h2>
<p>Customers today: <?= $customers ?></p>
<p>Income today: ₱<?= $income ?></p>
<p><a href="income_report.php">View Reports</a></p>
<p><a href="employees.php">Employee Stats</a></p>
<p><a href="marketing.php">Marketing Insights</a></p>
<p><a href="../logout.php">Logout</a></p>
