<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'barber') { header("Location: ../index.php"); exit; }

if(isset($_GET['id'])){
    $queue_id = $_GET['id'];
    $barber_id = $_SESSION['user']['id'];

    // Finish queue
    $conn->query("UPDATE queue SET status='finished' WHERE id=$queue_id");

    // Update performance table
    $time_served = rand(15,45); // placeholder: replace with actual service time
    $month = date('Ym');

    $perf = $conn->query("SELECT * FROM performance WHERE barber_id=$barber_id AND month='$month'");
    if($perf->num_rows > 0){
        $row = $perf->fetch_assoc();
        $new_count = $row['customers_served'] + 1;
        $new_avg = (($row['avg_service_time'] * $row['customers_served']) + $time_served) / $new_count;
        $conn->query("UPDATE performance SET customers_served=$new_count, avg_service_time=$new_avg WHERE id=".$row['id']);
    } else {
        $conn->query("INSERT INTO performance (barber_id, customers_served, avg_service_time, month) VALUES ($barber_id,1,$time_served,'$month')");
    }
}
header("Location: dashboard.php");
