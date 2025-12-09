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
    header("Location: students.php?error=Student ID not provided");
    exit();
}

$student_id = $_GET['id'];
$student = getStudentDetails($student_id);
$classes = getAllClasses();

if (!$student) {
    header("Location: students.php?error=Student not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    try {
        error_log("Form submitted for student edit - ID: " . $student_id);
        
        // Collect all form data with proper default values
        $student_data = [
            'int_name' => $_POST['int_name'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'admission_date' => $_POST['admission_date'] ?? '',
            'admission_no' => $_POST['admission_no'] ?? '',
            'current_class' => $_POST['current_class'] ?? NULL,
            'address' => $_POST['address'] ?? '',
            'father_name' => $_POST['father_name'] ?? '',
            'father_occupation' => $_POST['father_occupation'] ?? '',
            'father_phone' => $_POST['father_phone'] ?? '',
            'mother_name' => $_POST['mother_name'] ?? '',
            'mother_occupation' => $_POST['mother_occupation'] ?? '',
            'mother_phone' => $_POST['mother_phone'] ?? '',
            'guardian_name' => $_POST['guardian_name'] ?? '',
            'guardian_phone' => $_POST['guardian_phone'] ?? '',
            'religion' => $_POST['religion'] ?? '',
            'ethnicity' => $_POST['ethnicity'] ?? '',
            'birth_certificate_no' => $_POST['birth_certificate_no'] ?? '',
            'medical_info' => $_POST['medical_info'] ?? ''
        ];
        
        error_log("Student data to update: " . print_r($student_data, true));
        
        // Update student
        $update_result = updateStudentDetails($student_id, $student_data);
        
        if ($update_result) {
            header("Location: student_view.php?id=$student_id&success=Student updated successfully");
            exit();
        } else {
            $error = "Failed to update student. Please try again.";
            error_log("Update failed for student ID: " . $student_id);
        }
    } catch (Exception $e) {
        $error = "System error: " . $e->getMessage();
        error_log("Exception in student edit: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit <?php echo htmlspecialchars($student['int_name']); ?> - School Management System</title>
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
                <li class="hovered">
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
                <h2>Edit Student - <?php echo htmlspecialchars($student['int_name']); ?></h2>
                <div class="headerActions">
                    <a href="student_view.php?id=<?php echo $student_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="studentForm" name="submit" class="btn btn-primary">Update Student</button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="studentForm" method="POST" class="editForm">
                <div class="formSections">
                    
                    <!-- Student Personal Details -->
                    <div class="formSection">
                        <h3>Personal Details</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Name with Initials:*</label>
                                <input type="text" name="int_name" value="<?php echo htmlspecialchars($student['int_name']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Full Name:*</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Gender:*</label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="formGroup">
                                <label>Date of Birth:*</label>
                                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Admission Date:*</label>
                                <input type="date" name="admission_date" value="<?php echo htmlspecialchars($student['admission_date']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Admission No:</label>
                                <input type="text" name="admission_no" value="<?php echo htmlspecialchars($student['admission_no'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Current Class:</label>
                                <select name="current_class">
                                    <option value="">Select Class (Optional)</option>
                                    <?php foreach($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" 
                                            <?php echo ($student['current_class'] ?? '') == $class['class_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="formGroup full-width">
                                <label>Address:</label>
                                <textarea name="address" placeholder="Enter full address"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <div class="formSection">
                        <h3>Parent/Guardian Information</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Father's Name:</label>
                                <input type="text" name="father_name" value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Father's Occupation:</label>
                                <input type="text" name="father_occupation" value="<?php echo htmlspecialchars($student['father_occupation'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Father's Phone:</label>
                                <input type="tel" name="father_phone" value="<?php echo htmlspecialchars($student['father_phone'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Mother's Name:</label>
                                <input type="text" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Mother's Occupation:</label>
                                <input type="text" name="mother_occupation" value="<?php echo htmlspecialchars($student['mother_occupation'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Mother's Phone:</label>
                                <input type="tel" name="mother_phone" value="<?php echo htmlspecialchars($student['mother_phone'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Guardian's Name:</label>
                                <input type="text" name="guardian_name" value="<?php echo htmlspecialchars($student['guardian_name'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Guardian's Phone:</label>
                                <input type="tel" name="guardian_phone" value="<?php echo htmlspecialchars($student['guardian_phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="formSection">
                        <h3>Additional Information</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Religion:</label>
                                <input type="text" name="religion" value="<?php echo htmlspecialchars($student['religion'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Ethnicity:</label>
                                <input type="text" name="ethnicity" value="<?php echo htmlspecialchars($student['ethnicity'] ?? ''); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Birth Certificate No:</label>
                                <input type="text" name="birth_certificate_no" value="<?php echo htmlspecialchars($student['birth_certificate_no'] ?? ''); ?>">
                            </div>
                            <div class="formGroup full-width">
                                <label>Medical Information:</label>
                                <textarea name="medical_info" placeholder="Any medical conditions, allergies, or special needs"><?php echo htmlspecialchars($student['medical_info'] ?? ''); ?></textarea>
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
        document.getElementById('studentForm').addEventListener('submit', function(e) {
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