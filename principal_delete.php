<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: principals.php?error=Principal ID not provided");
    exit();
}

$principal_id = $_GET['id'];

// Delete principal
$delete_result = deletePrincipal($principal_id);

if ($delete_result) {
    header("Location: principals.php?success=Principal deleted successfully");
} else {
    header("Location: principals.php?error=Failed to delete principal");
}
exit();
?>