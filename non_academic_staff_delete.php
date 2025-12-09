<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: non_academic_staff.php?error=Staff ID not provided");
    exit();
}

$staff_id = $_GET['id'];

// Delete staff member
$delete_result = deleteNonAcademicStaff($staff_id);

if ($delete_result) {
    header("Location: non_academic_staff.php?success=Staff member deleted successfully");
} else {
    header("Location: non_academic_staff.php?error=Failed to delete staff member");
}
exit();
?>