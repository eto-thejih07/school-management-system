<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_students'])) {
        $student_ids = $_POST['selected_students'];
        $delete_result = deleteMultipleStudents($student_ids);
        
        if ($delete_result) {
            header("Location: students.php?success=" . urlencode(count($student_ids) . " students deleted successfully"));
            exit();
        } else {
            $error = "Failed to delete selected students. Please try again.";
        }
    } else {
        $error = "No students selected for deletion.";
    }
}

// Handle single delete via GET
if (isset($_GET['delete_id'])) {
    $delete_result = deleteStudent($_GET['delete_id']);
    
    if ($delete_result) {
        header("Location: students.php?success=" . urlencode("Student deleted successfully"));
        exit();
    } else {
        $error = "Failed to delete student. Please try again.";
    }
}

$students = getAllStudents();
$classes = getAllClasses(); // For filter dropdown
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students - School Management System</title>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <i class="fas fa-bars"></i>
                </div>
                <div class="user">
                    <img src="css/img/DMVLOGO.png" alt="User">
                </div>
            </div>

            <div class="sectionHeader">
                <h2>Students Management</h2>
                <div class="headerActions">
                    <a href="student_register.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Student
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmBulkDelete()" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="formGroup">
                        <label for="class_filter">Filter by Class:</label>
                        <select name="class_filter" id="class_filter" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            <?php foreach($classes as $class): ?>
                                <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo (isset($_GET['class_filter']) && $_GET['class_filter'] == $class['class_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <div class="content">
                <form id="bulkForm" method="POST">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Gender</th>
                                    <th>Admission Date</th>
                                    <th>Phone</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($students)): ?>
                                    <tr>
                                        <td colspan="8" class="no-data">
                                            <i class="fas fa-info-circle"></i> No students found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($students as $student): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_students[]" value="<?php echo htmlspecialchars($student['id']); ?>" class="student-checkbox">
                                        </td>
                                        <td><?php echo htmlspecialchars($student['id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['int_name']); ?></strong>
                                            <br><small><?php echo htmlspecialchars($student['full_name']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <span class="status <?php echo strtolower($student['gender']); ?>">
                                                <?php echo htmlspecialchars($student['gender']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['admission_date']); ?></td>
                                        <td><?php echo htmlspecialchars($student['father_phone'] ?? $student['mother_phone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn-action view" title="View Student">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="btn-text">View</span>
                                                </a>
                                                <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn-action edit" title="Edit Student">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="btn-text">Edit</span>
                                                </a>
                                                <button type="button" class="btn-action delete" 
                                                        onclick="confirmDelete('<?php echo htmlspecialchars($student['id']); ?>', '<?php echo htmlspecialchars(addslashes($student['int_name'])); ?>')"
                                                        title="Delete Student">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="btn-text">Delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="delete_selected" value="1">
                </form>
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

        // Bulk selection functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });

        // Update bulk delete button state
        function updateBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            bulkDeleteBtn.disabled = checkedBoxes.length === 0;
            
            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected (${checkedBoxes.length})`;
            } else {
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected`;
            }
        }

        // Add event listeners to all checkboxes
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkDeleteButton);
        });

        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            if (checkedBoxes.length > 0) {
                if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected student(s)? This action cannot be undone.`)) {
                    document.getElementById('bulkForm').submit();
                }
            }
        }

        // Confirm single delete
        function confirmDelete(studentId, studentName) {
            if (confirm(`Are you sure you want to delete student "${studentName}" (ID: ${studentId})? This action cannot be undone.`)) {
                window.location.href = `students.php?delete_id=${encodeURIComponent(studentId)}`;
            }
        }
    </script>
</body>
</html>