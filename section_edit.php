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
$teachers = getAvailableTeachers();

if (!$section) {
    header("Location: sections.php?error=Section not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    try {
        // Collect all form data
        $section_data = [
            'section_name' => $_POST['section_name'],
            'section_head_id' => $_POST['section_head_id'] ?? null,
            'description' => $_POST['description'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Update section
        $update_result = updateSectionDetails($section_id, $section_data);
        
        if ($update_result) {
            header("Location: section_view.php?id=$section_id&success=Section updated successfully");
            exit();
        } else {
            $error = "Failed to update section. Please try again.";
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
<title>Edit <?php echo $section['section_name']; ?> - School Management System</title>
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
                <h2>Edit Section - <?php echo $section['section_name']; ?></h2>
                <div class="headerActions">
                    <a href="section_view.php?id=<?php echo $section_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="sectionForm" name="submit" class="btn btn-primary">Update Section</button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="sectionForm" method="POST" class="editForm">
                <div class="formSections">
                    
                    <!-- Section Details -->
                    <div class="formSection">
                        <h3>Section Details</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Section Name:*</label>
                                <input type="text" name="section_name" value="<?php echo htmlspecialchars($section['section_name']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Section Head:</label>
                                <select name="section_head_id">
                                    <option value="">Select Section Head (Optional)</option>
                                    <?php foreach($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['teacher_id']; ?>" 
                                            <?php echo ($section['section_head_id'] ?? '') == $teacher['teacher_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($teacher['initials'] . ' - ' . $teacher['teacher_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="formGroup full-width">
                                <label>Description:</label>
                                <textarea name="description" rows="4"><?php echo htmlspecialchars($section['description']); ?></textarea>
                            </div>
                            <div class="formGroup">
                                <label>Status:</label>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $section['is_active'] ? 'checked' : ''; ?>>
                                    <label for="is_active" class="checkbox-label">Active Section</label>
                                </div>
                                <small style="color: #666; font-size: 12px;">Inactive sections won't be available for class assignment</small>
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
        document.getElementById('sectionForm').addEventListener('submit', function(e) {
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