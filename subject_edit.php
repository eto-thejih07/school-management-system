<?php
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

if (!$subject) {
    header("Location: subjects.php?error=Subject not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    try {
        // Collect all form data
        $subject_data = [
            'subject_name' => $_POST['subject_name'],
            'subject_code' => $_POST['subject_code'],
            'description' => $_POST['description'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Update subject
        $update_result = updateSubjectDetails($subject_id, $subject_data);
        
        if ($update_result) {
            header("Location: subject_view.php?id=$subject_id&success=Subject updated successfully");
            exit();
        } else {
            $error = "Failed to update subject. Please try again.";
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
<title>Edit <?php echo $subject['subject_name']; ?> - DMV Online</title>
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
                <h2>Edit Subject - <?php echo $subject['subject_name']; ?></h2>
                <div class="headerActions">
                    <a href="subject_view.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="subjectForm" name="submit" class="btn btn-primary">Update Subject</button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="subjectForm" method="POST" class="editForm">
                <div class="formSections">
                    
                    <!-- Subject Details -->
                    <div class="formSection">
                        <h3>Subject Details</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Subject Name:*</label>
                                <input type="text" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Subject Code:*</label>
                                <input type="text" name="subject_code" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" required>
                            </div>
                            <div class="formGroup full-width">
                                <label>Description:</label>
                                <textarea name="description" rows="4"><?php echo htmlspecialchars($subject['description']); ?></textarea>
                            </div>
                            <div class="formGroup">
                                <label>Status:</label>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo $subject['is_active'] ? 'checked' : ''; ?>>
                                    <label for="is_active" class="checkbox-label">Active Subject</label>
                                </div>
                                <small style="color: #666; font-size: 12px;">Inactive subjects won't be available for assignment</small>
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
        document.getElementById('subjectForm').addEventListener('submit', function(e) {
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