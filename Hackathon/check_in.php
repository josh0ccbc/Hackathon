<?php
include 'config/db.php';

// Get input from Arduino (POST or GET)
$code = isset($_POST['code']) ? $_POST['code'] : (isset($_GET['code']) ? $_GET['code'] : null);

if (!$code) {
    die(json_encode(['status' => 'error','message' => 'No code provided']));
}

// Step 1: Identify user by barcode or RFID
$user = $conn->query("SELECT id, name FROM users WHERE rfid_code='$code' OR barcode='$code'")->fetch_assoc();
if (!$user) {
    die(json_encode(['status' => 'error','message' => 'User not found']));
}
$customer_id = $user['id'];

// Step 2: Find today's appointment for this customer
date_default_timezone_set('Asia/Manila'); // PHT
$today = date('Y-m-d', strtotime('-1 day'));
$appointment = $conn->query("
    SELECT * FROM appointments 
    WHERE customer_id=$customer_id 
      AND DATE(appointment_time)='$today'
")->fetch_assoc();

if (!$appointment) {
    die(json_encode(['status' => 'error','message' => 'No appointment found today']));
}

// Step 3: Calculate queue number using MariaDB variable
$barber_filter = $appointment['barber_id'] ? "AND barber_id=" . $appointment['barber_id'] : "";

// Initialize row number
$conn->query("SET @rownum := 0");

// Generate queue numbers for today (appointments without queue_number)
$sql = "
    SELECT id, (@rownum := @rownum + 1) AS queue_number
    FROM appointments
    WHERE DATE(appointment_time)='$today'
      AND queue_number IS NULL
      $barber_filter
    ORDER BY appointment_time ASC, created_at ASC
";

$result = $conn->query($sql);
$queue_number = null;

while($row = $result->fetch_assoc()){
    if($row['id'] == $appointment['id']){
        $queue_number = $row['queue_number'];
        break;
    }
}

if($queue_number === null){
    die(json_encode(['status' => 'error','message' => 'Failed to assign queue number']));
}

// Step 4: Update appointment with queue_number
$conn->query("UPDATE appointments SET queue_number=$queue_number WHERE id=".$appointment['id']);

// Step 5: Add entry to queue table (or update if exists)
$existingQueue = $conn->query("SELECT * FROM queue WHERE customer_id=$customer_id AND DATE(created_at)='$today'")->fetch_assoc();
if(!$existingQueue){
    $barber_id = $appointment['barber_id'] ? $appointment['barber_id'] : 'NULL';
    $conn->query("INSERT INTO queue (id, customer_id, barber_id, status, created_at) VALUES ($queue_number, $customer_id, $barber_id, 'waiting', current_timestamp())");
}else{
    $conn->query("UPDATE queue SET queue_number=$queue_number, status='waiting' WHERE id=".$existingQueue['id']);
}

// Step 6: Return response
$response = [
    'status' => 'success',
    'customer_id' => $customer_id,
    'name' => $user['name'],
    'appointment_id' => $appointment['id'],
    'queue_number' => $queue_number,
    'barber_id' => $appointment['barber_id']
];

header('Content-Type: application/json');
echo json_encode($response);
