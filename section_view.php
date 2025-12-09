<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: sections.php?error=Section ID not provided");
    exit();
}

$section_id = $_GET['id'];
$section = getSectionDetails($section_id);
$section_classes = getClassesBySection($section_id);

if (!$section) {
    header("Location: sections.php?error=Section not found");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $section['section_name']; ?> - School Management System</title>
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
                    ☰
                </div>
                <div class="user">
                    <img src="css/img/DMVLOGO.png" alt="User">
                </div>
            </div>

            <div class="sectionHeader">
                <h2>Section Details - <?php echo $section['section_name']; ?></h2>
                <div class="headerActions">
                    <a href="sections.php" class="btn btn-secondary">Back to List</a>
                    <a href="section_edit.php?id=<?php echo $section_id; ?>" class="btn btn-primary">Edit Section</a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo $section_id; ?>')">Delete Section</button>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="success-message">
                    <?php echo $_GET['success']; ?>
                </div>
            <?php endif; ?>

            <div class="content">
                <div class="detail-sections">
                    <!-- Section Details -->
                    <div class="detail-section">
                        <h3>Section Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Section ID:</label>
                                <span><?php echo htmlspecialchars($section['section_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Section Name:</label>
                                <span><?php echo htmlspecialchars($section['section_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Section Head:</label>
                                <span>
                                    <?php if($section['section_head_name']): ?>
                                        <a href="teacher_view.php?id=<?php echo $section['section_head_id']; ?>" style="color: var(--blue); text-decoration: none;">
                                            <?php echo htmlspecialchars($section['section_head_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not Assigned</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span class="status <?php echo $section['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $section['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <?php if($section['description']): ?>
                            <div class="detail-item full-width">
                                <label>Description:</label>
                                <span><?php echo nl2br(htmlspecialchars($section['description'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <label>Total Classes:</label>
                                <span><?php echo count($section_classes); ?> classes</span>
                            </div>
                            <?php
                            $total_students = 0;
                            foreach($section_classes as $class) {
                                $total_students += $class['student_count'];
                            }
                            ?>
                            <div class="detail-item">
                                <label>Total Students:</label>
                                <span><?php echo $total_students; ?> students</span>
                            </div>
                        </div>
                    </div>

                    <!-- Classes in this Section -->
                    <?php if(!empty($section_classes)): ?>
                    <div class="detail-section">
                        <h3>Classes in this Section (<?php echo count($section_classes); ?>)</h3>
                        <div class="table-container">
<!-- Update the classes table in section_view.php -->
<table class="data-table">
    <thead>
        <tr>
            <th>Class Name</th>
            <th>Class Teacher</th>
            <th>Academic Year</th>
            <th>Students</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($section_classes as $class): ?>
        <tr>
            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
            <td>
                <?php if($class['class_teacher_name']): ?>
                    <?php echo htmlspecialchars($class['class_teacher_name']); ?>
                <?php else: ?>
                    <span class="text-muted">Not Assigned</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($class['academic_year']); ?></td>
            <td><?php echo htmlspecialchars($class['student_count']); ?> students</td>
            <td>
                <span class="status <?php echo $class['is_active'] ? 'active' : 'inactive'; ?>">
                    <?php echo $class['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
            </td>
            <td class="actions">
                <a href="class_view.php?id=<?php echo $class['class_id']; ?>" class="btn-action view">View</a>
                <a href="class_edit.php?id=<?php echo $class['class_id']; ?>" class="btn-action edit">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="detail-section">
                        <h3>Classes in this Section</h3>
                        <div class="no-data-message">
                            No classes are currently assigned to this section.
                            <br>
                            <a href="class_register.php?section=<?php echo $section_id; ?>" class="btn btn-primary" style="margin-top: 10px;">Add Class to this Section</a>
                        </div>
                    </div>
                    <?php endif; ?>
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

        // Confirm and delete section
        function confirmDelete(sectionId) {
            if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
                window.location.href = 'sections.php?delete_id=' + sectionId;
            }
        }
    </script>
</body>
</html>