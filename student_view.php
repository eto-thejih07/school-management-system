<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: students.php?error=Student ID not provided");
    exit();
}

$student_id = $_GET['id'];
$student = getStudentDetails($student_id);

if (!$student) {
    header("Location: students.php?error=Student not found");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $student['int_name']; ?> - School Management System</title>
<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <span class="img"><img src="css/img/DMVLOGO.png" alt="School Logo" style="width: 50px; height: auto; margin-top: 10px;"></span>
                        <span class="title" style="font-size: 1.5em;font-weight: 500; margin-top: 5px;">SCHOOL SYSTEM</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php">
                        <span class="icon">📊</span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="teachers.php">
                        <span class="icon">👨‍🏫</span>
                        <span class="title">Teachers</span>
                    </a>
                </li>
                <li >
                    <a href="principals.php">
                        <span class="icon">👔</span>
                        <span class="title">Principals</span>
                    </a>
                </li>
                <li >
                    <a href="non_academic_staff.php">
                        <span class="icon">👨‍💼</span>
                        <span class="title">Non-Academic Staff</span>
                    </a>
                </li>
                <li class="hovered">
                    <a href="students.php">
                        <span class="icon">🎓</span>
                        <span class="title">Students</span>
                    </a>
                </li>
                <li>
                    <a href="classes.php">
                        <span class="icon">📚</span>
                        <span class="title">Classes</span>
                    </a>
                </li>
                <li>
                    <a href="subjects.php">
                        <span class="icon">📖</span>
                        <span class="title">Subjects</span>
                    </a>
                </li>
                <li>
                    <a href="sections.php">
                        <span class="icon">🏢</span>
                        <span class="title">Sections</span>
                    </a>
                </li>
                <li><a href="buildings.php"><span class="icon">🏫</span><span class="title">Buildings</span></a></li>
                <li>
                    <a href="logout.php">
                        <span class="icon">🚪</span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    ☰
                </div>
                <div class="user">
                    <img src="css/img/DMVLOGO.png" alt="User">
                </div>
            </div>

            <div class="sectionHeader">
                <h2>Student Details - <?php echo $student['int_name']; ?></h2>
                <div class="headerActions">
                    <a href="students.php" class="btn btn-secondary">Back to List</a>
                    <a href="student_edit.php?id=<?php echo $student_id; ?>" class="btn btn-primary">Edit Student</a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo $student_id; ?>')">Delete Student</button>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="success-message">
                    <?php echo $_GET['success']; ?>
                </div>
            <?php endif; ?>

            <div class="content">
                <div class="detail-sections">
                    <!-- Student Personal Details -->
                    <div class="detail-section">
                        <h3>Personal Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Student ID:</label>
                                <span><?php echo htmlspecialchars($student['id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Name with Initials:</label>
                                <span><?php echo htmlspecialchars($student['int_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Full Name:</label>
                                <span><?php echo htmlspecialchars($student['full_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Gender:</label>
                                <span class="status <?php echo strtolower($student['gender']) === 'male' ? 'active' : 'inactive'; ?>">
                                    <?php echo htmlspecialchars($student['gender']); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Date of Birth:</label>
                                <span><?php echo htmlspecialchars($student['date_of_birth']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Admission Date:</label>
                                <span><?php echo htmlspecialchars($student['admission_date']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Admission No:</label>
                                <span><?php echo !empty($student['admission_no']) ? htmlspecialchars($student['admission_no']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Current Class:</label>
                                <span>
                                    <?php if(!empty($student['class_name'])): ?>
                                        <a href="class_view.php?id=<?php echo $student['class_id']; ?>" style="color: var(--blue); text-decoration: none;">
                                            <?php echo htmlspecialchars($student['class_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not Assigned</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Address:</label>
                                <span><?php echo !empty($student['address']) ? nl2br(htmlspecialchars($student['address'])) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <div class="detail-section">
                        <h3>Parent/Guardian Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Father's Name:</label>
                                <span><?php echo !empty($student['father_name']) ? htmlspecialchars($student['father_name']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Father's Occupation:</label>
                                <span><?php echo !empty($student['father_occupation']) ? htmlspecialchars($student['father_occupation']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Father's Phone:</label>
                                <span><?php echo !empty($student['father_phone']) ? htmlspecialchars($student['father_phone']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Mother's Name:</label>
                                <span><?php echo !empty($student['mother_name']) ? htmlspecialchars($student['mother_name']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Mother's Occupation:</label>
                                <span><?php echo !empty($student['mother_occupation']) ? htmlspecialchars($student['mother_occupation']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Mother's Phone:</label>
                                <span><?php echo !empty($student['mother_phone']) ? htmlspecialchars($student['mother_phone']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Guardian's Name:</label>
                                <span><?php echo !empty($student['guardian_name']) ? htmlspecialchars($student['guardian_name']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Guardian's Phone:</label>
                                <span><?php echo !empty($student['guardian_phone']) ? htmlspecialchars($student['guardian_phone']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="detail-section">
                        <h3>Additional Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Religion:</label>
                                <span><?php echo !empty($student['religion']) ? htmlspecialchars($student['religion']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Ethnicity:</label>
                                <span><?php echo !empty($student['ethnicity']) ? htmlspecialchars($student['ethnicity']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Birth Certificate No:</label>
                                <span><?php echo !empty($student['birth_certificate_no']) ? htmlspecialchars($student['birth_certificate_no']) : '<span class="text-muted">Not provided</span>'; ?></span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Medical Information:</label>
                                <span><?php echo !empty($student['medical_info']) ? nl2br(htmlspecialchars($student['medical_info'])) : '<span class="text-muted">No medical information provided</span>'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navigation toggle
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function(){
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }

        // Confirm and delete student
        function confirmDelete(studentId) {
            if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
                window.location.href = 'students.php?delete_id=' + studentId;
            }
        }
    </script>
</body>
</html>