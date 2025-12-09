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
    if (!empty($_POST['selected_classes'])) {
        $class_ids = $_POST['selected_classes'];
        $delete_result = deleteMultipleClasses($class_ids);
        
        if ($delete_result) {
            header("Location: classes.php?success=" . urlencode(count($class_ids) . " classes deleted successfully"));
            exit();
        } else {
            $error = "Failed to delete selected classes. Please try again.";
        }
    } else {
        $error = "No classes selected for deletion.";
    }
}

// Handle single delete via GET
if (isset($_GET['delete_id'])) {
    $delete_result = deleteClass($_GET['delete_id']);
    
    if ($delete_result) {
        header("Location: classes.php?success=" . urlencode("Class deleted successfully"));
        exit();
    } else {
        $error = "Failed to delete class. Please try again.";
    }
}

$classes = getAllClasses();
$sections = getAllSections();
$teachers = getAvailableTeachers();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Classes - School Management System</title>
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
                    <i class="fas fa-bars"></i>
                </div>
                <div class="user">
                    <img src="css/img/DMVLOGO.png" alt="User">
                </div>
            </div>

            <div class="sectionHeader">
                <h2>Classes Management</h2>
                <div class="headerActions">
                    <a href="class_register.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Class
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

            <div class="content">
                <form id="bulkForm" method="POST">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Class ID</th>
                                    <th>Class Name</th>
                                    <th>Section</th>
                                    <th>Class Teacher</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($classes)): ?>
                                    <tr>
                                        <td colspan="8" class="no-data">
                                            <i class="fas fa-info-circle"></i> No classes found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($classes as $class): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_classes[]" value="<?php echo htmlspecialchars($class['class_id']); ?>" class="class-checkbox">
                                        </td>
                                        <td><?php echo htmlspecialchars($class['class_id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($class['class_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($class['section_name'] ?? 'Not Assigned'); ?></td>
                                        <td><?php echo htmlspecialchars($class['class_teacher_name'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <span class="badge"><?php echo htmlspecialchars($class['student_count'] ?? 0); ?> students</span>
                                        </td>
                                        <td>
                                            <span class="status <?php echo ($class['is_active'] == 1) ? 'active' : 'inactive'; ?>">
                                                <?php echo ($class['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="class_view.php?id=<?php echo $class['class_id']; ?>" class="btn-action view" title="View Class">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="btn-text">View</span>
                                                </a>
                                                <a href="class_edit.php?id=<?php echo $class['class_id']; ?>" class="btn-action edit" title="Edit Class">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="btn-text">Edit</span>
                                                </a>
                                                <button type="button" class="btn-action delete" 
                                                        onclick="confirmDelete('<?php echo htmlspecialchars($class['class_id']); ?>', '<?php echo htmlspecialchars(addslashes($class['class_name'])); ?>')"
                                                        title="Delete Class">
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
            const checkboxes = document.querySelectorAll('.class-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });

        // Update bulk delete button state
        function updateBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.class-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            bulkDeleteBtn.disabled = checkedBoxes.length === 0;
            
            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected (${checkedBoxes.length})`;
            } else {
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected`;
            }
        }

        // Add event listeners to all checkboxes
        document.querySelectorAll('.class-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkDeleteButton);
        });

        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkedBoxes = document.querySelectorAll('.class-checkbox:checked');
            if (checkedBoxes.length > 0) {
                if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected class(es)? This will also remove all students from these classes.`)) {
                    document.getElementById('bulkForm').submit();
                }
            }
        }

        // Confirm single delete
        function confirmDelete(classId, className) {
            if (confirm(`Are you sure you want to delete class "${className}" (ID: ${classId})? This will remove all students from this class.`)) {
                window.location.href = `classes.php?delete_id=${encodeURIComponent(classId)}`;
            }
        }
    </script>

    <style>
        .badge {
            background: var(--blue);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
    </style>
</body>
</html>