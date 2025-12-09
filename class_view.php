<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: classes.php?error=Class ID not provided");
    exit();
}

$class_id = $_GET['id'];
$class = getClassDetails($class_id);
$students = getStudentsInClass($class_id);

if (!$class) {
    header("Location: classes.php?error=Class not found");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $class['class_name']; ?> - School Management System</title>
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
                <li>
                    <a href="principals.php">
                        <span class="icon">👔</span>
                        <span class="title">Principals</span>
                    </a>
                </li>
                <li>
                    <a href="non_academic_staff.php">
                        <span class="icon">👨‍💼</span>
                        <span class="title">Non-Academic Staff</span>
                    </a>
                </li>
                <li>
                    <a href="students.php">
                        <span class="icon">🎓</span>
                        <span class="title">Students</span>
                    </a>
                </li>
                <li class="hovered">
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
                <h2>Class Details - <?php echo $class['class_name']; ?></h2>
                <div class="headerActions">
                    <a href="classes.php" class="btn btn-secondary">Back to List</a>
                    <a href="class_edit.php?id=<?php echo $class_id; ?>" class="btn btn-primary">Edit Class</a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo $class_id; ?>')">Delete Class</button>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="success-message">
                    <?php echo $_GET['success']; ?>
                </div>
            <?php endif; ?>

            <div class="content">
                <div class="detail-sections">
                    <!-- Class Details -->
                    <div class="detail-section">
                        <h3>Class Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Class ID:</label>
                                <span><?php echo htmlspecialchars($class['class_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Class Name:</label>
                                <span><?php echo htmlspecialchars($class['class_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Section:</label>
                                <span>
                                    <a href="section_view.php?id=<?php echo $class['section_id']; ?>" style="color: var(--blue); text-decoration: none;">
                                        <?php echo htmlspecialchars($class['section_name']); ?>
                                    </a>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Class Teacher:</label>
                                <span>
                                    <?php if($class['class_teacher_name']): ?>
                                        <a href="teacher_view.php?id=<?php echo $class['class_teacher_id']; ?>" style="color: var(--blue); text-decoration: none;">
                                            <?php echo htmlspecialchars($class['class_teacher_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not Assigned</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Academic Year:</label>
                                <span><?php echo htmlspecialchars($class['academic_year']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Total Students:</label>
                                <span><?php echo count($students); ?> students</span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span class="status <?php echo $class['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $class['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Students in this Class -->
                    <div class="detail-section">
                        <h3>Students in this Class (<?php echo count($students); ?>)</h3>
                        <?php if(!empty($students)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name with Initials</th>
                                        <th>Full Name</th>
                                        <th>Gender</th>
                                        <th>Date of Birth</th>
                                        <th>Admission Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['int_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td>
                                            <span class="status <?php echo strtolower($student['gender']) === 'male' ? 'active' : 'inactive'; ?>">
                                                <?php echo htmlspecialchars($student['gender']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                        <td><?php echo htmlspecialchars($student['admission_date']); ?></td>
                                        <td class="actions">
                                            <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn-action view">View</a>
                                            <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn-action edit">Edit</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="no-data-message">
                            No students are currently enrolled in this class.
                            <br>
                            <a href="student_register.php?class=<?php echo $class_id; ?>" class="btn btn-primary" style="margin-top: 10px;">Add Student to this Class</a>
                        </div>
                        <?php endif; ?>
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

        // Confirm and delete class
        function confirmDelete(classId) {
            if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
                window.location.href = 'classes.php?delete_id=' + classId;
            }
        }
    </script>
</body>
</html>