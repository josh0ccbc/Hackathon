<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'barber') { header("Location: ../index.php"); exit; }

if(isset($_GET['id'])){
    $queue_id = $_GET['id'];
    $conn->query("UPDATE queue SET status='in_service' WHERE id=$queue_id");
}
header("Location: dashboard.php");
