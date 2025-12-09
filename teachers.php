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
    if (!empty($_POST['selected_teachers'])) {
        $teacher_ids = $_POST['selected_teachers'];
        $delete_result = deleteMultipleTeachers($teacher_ids);
        
        if ($delete_result) {
            header("Location: teachers.php?success=" . urlencode(count($teacher_ids) . " teachers deleted successfully"));
            exit();
        } else {
            $error = "Failed to delete selected teachers. Please try again.";
        }
    } else {
        $error = "No teachers selected for deletion.";
    }
}

// Handle single delete via GET
if (isset($_GET['delete_id'])) {
    $delete_result = deleteTeacher($_GET['delete_id']);
    
    if ($delete_result) {
        header("Location: teachers.php?success=" . urlencode("Teacher deleted successfully"));
        exit();
    } else {
        $error = "Failed to delete teacher. Please try again.";
    }
}

$teachers = getAllTeachers();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teachers - School Management System</title>
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
                <li class="hovered">
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
                <li>
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
                <h2>Teachers Management</h2>
                <div class="headerActions">
                    <a href="teacher_register.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Teacher
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
                                    <th>Teacher ID</th>
                                    <th>Name</th>
                                    <th>Title</th>
                                    <th>NIC</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($teachers)): ?>
                                    <tr>
                                        <td colspan="8" class="no-data">
                                            <i class="fas fa-info-circle"></i> No teachers found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($teachers as $teacher): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_teachers[]" value="<?php echo htmlspecialchars($teacher['teacher_id']); ?>" class="teacher-checkbox">
                                        </td>
                                        <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($teacher['initials']); ?></strong>
                                            <br><small><?php echo htmlspecialchars($teacher['teacher_name']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($teacher['title']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['nic']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['pos']); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="teacher_view.php?id=<?php echo $teacher['teacher_id']; ?>" class="btn-action view" title="View Teacher">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="btn-text">View</span>
                                                </a>
                                                <a href="teacher_edit.php?id=<?php echo $teacher['teacher_id']; ?>" class="btn-action edit" title="Edit Teacher">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="btn-text">Edit</span>
                                                </a>
                                                <button type="button" class="btn-action delete" 
                                                        onclick="confirmDelete('<?php echo htmlspecialchars($teacher['teacher_id']); ?>', '<?php echo htmlspecialchars(addslashes($teacher['initials'])); ?>')"
                                                        title="Delete Teacher">
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
            const checkboxes = document.querySelectorAll('.teacher-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });

        // Update bulk delete button state
        function updateBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.teacher-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            bulkDeleteBtn.disabled = checkedBoxes.length === 0;
            
            // Update button text with count
            if (checkedBoxes.length > 0) {
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected (${checkedBoxes.length})`;
            } else {
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected`;
            }
        }

        // Add event listeners to all checkboxes
        document.querySelectorAll('.teacher-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkDeleteButton);
        });

        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkedBoxes = document.querySelectorAll('.teacher-checkbox:checked');
            if (checkedBoxes.length > 0) {
                if (confirm(`Are you sure you want to delete ${checkedBoxes.length} selected teacher(s)? This action cannot be undone.`)) {
                    document.getElementById('bulkForm').submit();
                }
            }
        }

        // Confirm single delete
        function confirmDelete(teacherId, teacherName) {
            if (confirm(`Are you sure you want to delete teacher "${teacherName}" (ID: ${teacherId})? This action cannot be undone.`)) {
                window.location.href = `teachers.php?delete_id=${encodeURIComponent(teacherId)}`;
            }
        }

        // Quick view function (optional - for modal view)
        function quickView(teacherId) {
            // You can implement a modal view here
            window.location.href = `teacher_view.php?teacher_id=${encodeURIComponent(teacherId)}`;
        }

        // Quick edit function (optional - for modal edit)
        function quickEdit(teacherId) {
            window.location.href = `teacher_edit.php?teacher_id=${encodeURIComponent(teacherId)}`;
        }
    </script>

    <style>
        /* Additional styles for better appearance */
        .data-table td small {
            color: #666;
            font-size: 0.85em;
        }
        
        .success-message,
        .error-message {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 20px;
            padding: 12px 15px;
            border-radius: 8px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .navigation .icon i {
            width: 20px;
            text-align: center;
        }
    </style>
</body>
</html>