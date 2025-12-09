<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

// Get all non-academic staff
$staff = getAllNonAcademicStaff();

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_delete'])) {
    if (!empty($_POST['selected_staff'])) {
        $staff_ids = $_POST['selected_staff'];
        $delete_result = deleteMultipleNonAcademicStaff($staff_ids);
        
        if ($delete_result) {
            header("Location: non_academic_staff.php?success=Selected staff members deleted successfully");
            exit();
        } else {
            $error = "Failed to delete selected staff members.";
        }
    } else {
        $error = "No staff members selected for deletion.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Non-Academic Staff - DMV Online</title>
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
                <li class="hovered">
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
                    ☰
                </div>
                <div class="user">
                    <img src="css/img/DMVLOGO.png" alt="User">
                </div>
            </div>

            <div class="sectionHeader">
                <h2>Non-Academic Staff Management</h2>
                <div class="headerActions">
                    <a href="non_academic_staff_register.php" class="btn btn-primary">Add New Staff</a>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success'])): ?>
                <div class="success-message">
                    <?php echo $_GET['success']; ?>
                </div>
            <?php endif; ?>

            <div class="content">
                <?php if(!empty($staff)): ?>
                <form method="POST" id="bulkForm">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Staff ID</th>
                                    <th>Name with Initials</th>
                                    <th>Full Name</th>
                                    <th>NIC</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($staff as $staff_member): ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="selected_staff[]" value="<?php echo $staff_member['staff_id']; ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($staff_member['staff_id']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['initials']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['staff_name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($staff_member['pos']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="non_academic_staff_view.php?id=<?php echo $staff_member['staff_id']; ?>" class="btn-action view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="non_academic_staff_edit.php?id=<?php echo $staff_member['staff_id']; ?>" class="btn-action edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn-action delete" onclick="confirmDelete('<?php echo $staff_member['staff_id']; ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 20px; display: flex; gap: 10px; align-items: center;">
                        <button type="submit" name="bulk_delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the selected staff members?')">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        <span id="selectedCount" style="color: var(--black2); font-size: 14px;">0 staff members selected</span>
                    </div>
                </form>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users"></i>
                    <p>No non-academic staff found. <a href="non_academic_staff_register.php">Add the first staff member</a></p>
                </div>
                <?php endif; ?>
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

        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_staff[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Update selected count
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('input[name="selected_staff[]"]:checked');
            document.getElementById('selectedCount').textContent = checkboxes.length + ' staff members selected';
        }

        // Add event listeners to all checkboxes
        document.querySelectorAll('input[name="selected_staff[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Confirm delete single staff member
        function confirmDelete(staffId) {
            if (confirm('Are you sure you want to delete this staff member? This action cannot be undone.')) {
                window.location.href = 'non_academic_staff_delete.php?id=' + staffId;
            }
        }
    </script>
</body>
</html>