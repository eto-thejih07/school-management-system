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
$sections = getAllSections();
$teachers = getAvailableTeachers();

if (!$class) {
    header("Location: classes.php?error=Class not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    try {
        // Collect all form data
        $class_data = [
            'class_name' => $_POST['class_name'],
            'section_id' => $_POST['section_id'],
            'class_teacher_id' => $_POST['class_teacher_id'] ?? null,
            'academic_year' => $_POST['academic_year'],
            'capacity' => $_POST['capacity'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Update class
        $update_result = updateClassDetails($class_id, $class_data);
        
        if ($update_result) {
            header("Location: class_view.php?id=$class_id&success=Class updated successfully");
            exit();
        } else {
            $error = "Failed to update class. Please try again.";
        }
    } catch (Exception $e) {
        $error = "System error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit <?php echo $class['class_name']; ?> - School Management System</title>
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
                <h2>Edit Class - <?php echo $class['class_name']; ?></h2>
                <div class="headerActions">
                    <a href="class_view.php?id=<?php echo $class_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="classForm" name="submit" class="btn btn-primary">Update Class</button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="classForm" method="POST" class="editForm">
               <!-- Replace the form section in class_edit.php -->
<div class="formSection">
    <h3>Class Details</h3>
    <div class="formGrid">
        <div class="formGroup">
            <label>Class Name:*</label>
            <input type="text" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
        </div>
        <div class="formGroup">
            <label>Section:*</label>
            <select name="section_id" required>
                <option value="">Select Section</option>
                <?php foreach($sections as $section): ?>
                    <option value="<?php echo $section['section_id']; ?>" 
                        <?php echo $class['section_id'] == $section['section_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($section['section_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="formGroup">
            <label>Class Teacher:</label>
            <select name="class_teacher_id">
                <option value="">Select Class Teacher (Optional)</option>
                <?php foreach($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['teacher_id']; ?>" 
                        <?php echo ($class['class_teacher_id'] ?? '') == $teacher['teacher_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($teacher['initials'] . ' - ' . $teacher['teacher_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="formGroup">
            <label>Academic Year:*</label>
            <input type="number" name="academic_year" value="<?php echo htmlspecialchars($class['academic_year']); ?>" required min="2000" max="2030">
        </div>
        <div class="formGroup">
            <label>Status:</label>
            <div class="checkbox-group">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $class['is_active'] ? 'checked' : ''; ?>>
                <label for="is_active" class="checkbox-label">Active Class</label>
            </div>
            <small style="color: #666; font-size: 12px;">Inactive classes won't be available for student assignment</small>
        </div>
    </div>
</div>
                </div>
            </form>
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

        // Form validation
        document.getElementById('classForm').addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = 'var(--danger)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>