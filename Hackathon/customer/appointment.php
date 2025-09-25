<?php
session_start();
include '../config/db.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') { header("Location: ../index.php"); exit; }

if(isset($_POST['book'])){
    $customer_id = $_SESSION['user']['id'];
    $barber_id = $_POST['barber_id'] ?: "NULL";
    $time = $_POST['time'];
    $conn->query("INSERT INTO appointments (customer_id, barber_id, appointment_time) VALUES ($customer_id, $barber_id, '$time')");
    $success = "Appointment booked successfully!";
}

// Fetch barbers
$barbers = $conn->query("SELECT id, name FROM users WHERE role='barber'");
?>
<h2>Book Appointment</h2>
<p><a href="dashboard.php">Back to Dashboard</a></p>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<form method="POST">
    <label>Barber (optional)</label>
    <select name="barber_id">
        <option value="">Any</option>
        <?php while($b=$barbers->fetch_assoc()): ?>
            <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
        <?php endwhile; ?>
    </select><br>
    <label>Time</label>
    <input type="datetime-local" name="time" required><br>
    <button type="submit" name="book">Book</button>
</form>
