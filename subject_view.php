<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: subjects.php?error=Subject ID not provided");
    exit();
}

$subject_id = $_GET['id'];
$subject = getSubjectDetails($subject_id);
$subject_teachers = getSubjectTeachers($subject_id);

if (!$subject) {
    header("Location: subjects.php?error=Subject not found");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $subject['subject_name']; ?> - DMV Online</title>
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
                <li class="hovered">
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
                <h2>Subject Details - <?php echo $subject['subject_name']; ?></h2>
                <div class="headerActions">
                    <a href="subjects.php" class="btn btn-secondary">Back to List</a>
                    <a href="subject_edit.php?id=<?php echo $subject_id; ?>" class="btn btn-primary">Edit Subject</a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo $subject_id; ?>')">Delete Subject</button>
                </div>
            </div>

            <div class="content">
                <div class="detail-sections">
                    <!-- Subject Details -->
                    <div class="detail-section">
                        <h3>Subject Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Subject ID:</label>
                                <span><?php echo htmlspecialchars($subject['subject_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Subject Name:</label>
                                <span><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Subject Code:</label>
                                <span><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span class="status <?php echo $subject['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $subject['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <?php if($subject['description']): ?>
                            <div class="detail-item full-width">
                                <label>Description:</label>
                                <span><?php echo nl2br(htmlspecialchars($subject['description'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Assigned Teachers -->
                    <?php if(!empty($subject_teachers)): ?>
                    <div class="detail-section">
                        <h3>Assigned Teachers (<?php echo count($subject_teachers); ?>)</h3>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Teacher ID</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($subject_teachers as $teacher): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                        <td>
                                            <a href="teacher_view.php?id=<?php echo $teacher['teacher_id']; ?>" style="color: var(--blue); text-decoration: none;">
                                                <?php echo htmlspecialchars($teacher['initials']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                                        <td>
                                            <?php if($teacher['is_primary']): ?>
                                                <span class="status active">Primary</span>
                                            <?php else: ?>
                                                <span class="status">Additional</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="detail-section">
                        <h3>Assigned Teachers</h3>
                        <div class="no-data-message">
                            No teachers are currently assigned to this subject.
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

        // Confirm and delete subject
        function confirmDelete(subjectId) {
            if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
                window.location.href = 'subjects.php?delete_id=' + subjectId;
            }
        }
    </script>
</body>
</html>