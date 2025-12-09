<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

$sections = getAllSections();
$teachers = getAvailableTeachers();
$current_year = date('Y');

// Handle pre-selected section from section view page
$preselected_section = isset($_GET['section']) ? $_GET['section'] : null;

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
        
        // Insert class
        $insert_result = addNewClass($class_data);
        
        if ($insert_result) {
            header("Location: classes.php?success=Class registered successfully");
            exit();
        } else {
            $error = "Failed to register class. Please try again.";
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
<title>Register Class - School Management System</title>
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
                <h2>Register New Class</h2>
                <div class="headerActions">
                    <a href="classes.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="classForm" name="submit" class="btn btn-primary">Register Class</button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="classForm" method="POST" class="editForm">
                <div class="formSections">
                    
                    <!-- Class Details -->
                   <!-- Replace the form section in class_register.php -->
<div class="formSection">
    <h3>Class Details</h3>
    <div class="formGrid">
        <div class="formGroup">
            <label>Class Name:*</label>
            <input type="text" name="class_name" value="<?php echo $_POST['class_name'] ?? ''; ?>" required placeholder="e.g., Grade 1A, Grade 2B">
        </div>
        <div class="formGroup">
            <label>Section:*</label>
            <select name="section_id" required>
                <option value="">Select Section</option>
                <?php foreach($sections as $section): ?>
                    <option value="<?php echo $section['section_id']; ?>" 
                        <?php echo ($_POST['section_id'] ?? $preselected_section) == $section['section_id'] ? 'selected' : ''; ?>>
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
                        <?php echo ($_POST['class_teacher_id'] ?? '') == $teacher['teacher_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($teacher['initials'] . ' - ' . $teacher['teacher_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="formGroup">
            <label>Academic Year:*</label>
            <input type="number" name="academic_year" value="<?php echo $_POST['academic_year'] ?? $current_year; ?>" required min="2000" max="2030">
        </div>
        <div class="formGroup">
            <label>Status:</label>
            <div class="checkbox-group">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
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