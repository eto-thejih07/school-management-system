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
    if (!empty($_POST['selected_sections'])) {
        // Note: We're not implementing bulk delete for sections since you only have 3
        $error = "Bulk deletion is not allowed for sections.";
    } else {
        $error = "No sections selected for deletion.";
    }
}

// Handle single delete via GET
if (isset($_GET['delete_id'])) {
    $delete_result = false; // We don't implement section deletion since you only have 3
    $error = "Section deletion is not allowed.";
}

$sections = getAllSections();
$teachers = getAvailableTeachers();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sections - School Management System</title>
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
                <li class="hovered">
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
                <h2>Sections Management</h2>
                <div class="headerActions">
                    <a href="section_register.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Section
                    </a>
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
                                    <th>Section ID</th>
                                    <th>Section Name</th>
                                    <th>Section Head</th>
                                    <th>Classes</th>
                                    <th>Total Students</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($sections)): ?>
                                    <tr>
                                        <td colspan="9" class="no-data">
                                            <i class="fas fa-info-circle"></i> No sections found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($sections as $section): ?>
                                    <?php
                                    $section_classes = getClassesBySection($section['section_id']);
                                    $total_students = 0;
                                    foreach($section_classes as $class) {
                                        $total_students += $class['student_count'];
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_sections[]" value="<?php echo htmlspecialchars($section['section_id']); ?>" class="section-checkbox">
                                        </td>
                                        <td><?php echo htmlspecialchars($section['section_id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($section['section_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($section['section_head_name']): ?>
                                                <?php echo htmlspecialchars($section['section_head_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge"><?php echo count($section_classes); ?> classes</span>
                                        </td>
                                        <td>
                                            <span class="badge"><?php echo $total_students; ?> students</span>
                                        </td>
                                        <td>
                                            <?php 
                                            $description = $section['description'];
                                            if ($description && strlen($description) > 50) {
                                                echo htmlspecialchars(substr($description, 0, 50)) . '...';
                                            } else if ($description) {
                                                echo htmlspecialchars($description);
                                            } else {
                                                echo '<span class="text-muted">No description</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="status <?php echo ($section['is_active'] == 1) ? 'active' : 'inactive'; ?>">
                                                <?php echo ($section['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="section_view.php?id=<?php echo $section['section_id']; ?>" class="btn-action view" title="View Section">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="btn-text">View</span>
                                                </a>
                                                <a href="section_edit.php?id=<?php echo $section['section_id']; ?>" class="btn-action edit" title="Edit Section">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="btn-text">Edit</span>
                                                </a>
                                                <a href="classes.php?section=<?php echo urlencode($section['section_id']); ?>" class="btn-action primary" title="View Classes">
                                                    <i class="fas fa-book"></i>
                                                    <span class="btn-text">Classes</span>
                                                </a>
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

        // Bulk selection functionality (optional - since deletion is disabled)
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.section-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Add event listeners to all checkboxes
        document.querySelectorAll('.section-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Optional: You can add bulk action functionality here if needed
            });
        });
    </script>

    <style>
        .text-muted {
            color: var(--black2);
            font-style: italic;
        }
        
        .badge {
            background: var(--blue);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .btn-action.primary {
            background: var(--info);
            color: var(--white);
        }
        
        .btn-action.primary:hover {
            background: #138496;
            box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
        }
    </style>
</body>
</html>