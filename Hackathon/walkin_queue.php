<?php
include 'config/db.php';

// Data from Arduino or frontend
$barber_id = isset($_GET['barber_id']) && !empty($_GET['barber_id']) ? intval($_GET['barber_id']) : NULL;

// Insert new queue
$stmt = $conn->prepare("INSERT INTO queue (customer_id, barber_id, status) VALUES (NULL, ?, 'waiting')");
$stmt->bind_param("i", $barber_id);
$stmt->execute();

// Get assigned queue number
$queue_id = $stmt->insert_id;

// Response
$response = [
    'queue_number' => $queue_id,
    'barber_id' => $barber_id,
    'status' => 'waiting'
];

header('Content-Type: application/json');
echo json_encode($response);
