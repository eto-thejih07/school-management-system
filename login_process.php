<?php
include "db.php";
session_start();

// Check if query executes successfully
$result = $conn->query("SELECT username, pw FROM users");
if (!$result) {
    die("Database query failed: " . $conn->error);
}

// Check if any users exist
if ($result->num_rows === 0) {
    die("No users found in database");
}

$credentials = $result->fetch_assoc();
$admin_username = $credentials['username'];
$admin_password = $credentials['pw'];

if ($_POST['username'] === $admin_username && $_POST['pw'] === $admin_password) {
    $_SESSION['admin_logged_in'] = true;
    header("Location: dashboard.php");
    exit();
} else {
    echo "<script>alert('Invalid username or password!'); window.location.href='login.php';</script>";
    exit();
}
?>
