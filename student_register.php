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

$classes = getAllClasses();
$current_date = date('Y-m-d');

// Handle pre-selected class from class view page
$preselected_class = isset($_GET['class']) ? $_GET['class'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    try {
        error_log("=== FORM SUBMISSION START ===");
        error_log("POST data: " . print_r($_POST, true));
        
        // Collect all form data with proper default values
        $student_data = [
            'int_name' => $_POST['int_name'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'admission_date' => $_POST['admission_date'] ?? '',
            'admission_no' => $_POST['admission_no'] ?? '',
            'current_class' => !empty($_POST['current_class']) ? $_POST['current_class'] : NULL,
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
        
        error_log("Processed student_data: " . print_r($student_data, true));
        
        // Insert student
        $insert_result = addNewStudent($student_data);
        
        if ($insert_result) {
            error_log("✅ Registration successful, redirecting...");
            header("Location: students.php?success=Student registered successfully");
            exit();
        } else {
            $error = "Failed to register student. Please try again.";
            error_log("❌ Registration failed in addNewStudent function");
        }
        
        error_log("=== FORM SUBMISSION END ===");
        
    } catch (Exception $e) {
        $error = "System error: " . $e->getMessage();
        error_log("💥 Exception in form handler: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Student - School Management System</title>
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
                <h2>Register New Student</h2>
                <div class="headerActions">
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="studentForm" name="submit" class="btn btn-primary">Register Student</button>
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
                                <input type="text" name="int_name" value="<?php echo htmlspecialchars($_POST['int_name'] ?? ''); ?>" required placeholder="e.g., A.B.C. Perera">
                            </div>
                            <div class="formGroup">
                                <label>Full Name:*</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required placeholder="e.g., Amal Bandara Chandana Perera">
                            </div>
                            <div class="formGroup">
                                <label>Gender:*</label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($_POST['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($_POST['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="formGroup">
                                <label>Date of Birth:*</label>
                                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Admission Date:*</label>
                                <input type="date" name="admission_date" value="<?php echo htmlspecialchars($_POST['admission_date'] ?? $current_date); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Admission No:</label>
                                <input type="text" name="admission_no" value="<?php echo htmlspecialchars($_POST['admission_no'] ?? ''); ?>" placeholder="Optional">
                            </div>
                            <div class="formGroup">
                                <label>Current Class:</label>
                                <select name="current_class">
                                    <option value="">Select Class (Optional)</option>
                                    <?php foreach($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" 
                                            <?php echo ($_POST['current_class'] ?? $preselected_class) == $class['class_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="formGroup full-width">
                                <label>Address:</label>
                                <textarea name="address" placeholder="Enter full address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Parent/Guardian Information -->
                    <div class="formSection">
                        <h3>Parent/Guardian Information</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Father's Name:</label>
                                <input type="text" name="father_name" value="<?php echo htmlspecialchars($_POST['father_name'] ?? ''); ?>" placeholder="Father's full name">
                            </div>
                            <div class="formGroup">
                                <label>Father's Occupation:</label>
                                <input type="text" name="father_occupation" value="<?php echo htmlspecialchars($_POST['father_occupation'] ?? ''); ?>" placeholder="Occupation">
                            </div>
                            <div class="formGroup">
                                <label>Father's Phone:</label>
                                <input type="tel" name="father_phone" value="<?php echo htmlspecialchars($_POST['father_phone'] ?? ''); ?>" placeholder="Phone number">
                            </div>
                            <div class="formGroup">
                                <label>Mother's Name:</label>
                                <input type="text" name="mother_name" value="<?php echo htmlspecialchars($_POST['mother_name'] ?? ''); ?>" placeholder="Mother's full name">
                            </div>
                            <div class="formGroup">
                                <label>Mother's Occupation:</label>
                                <input type="text" name="mother_occupation" value="<?php echo htmlspecialchars($_POST['mother_occupation'] ?? ''); ?>" placeholder="Occupation">
                            </div>
                            <div class="formGroup">
                                <label>Mother's Phone:</label>
                                <input type="tel" name="mother_phone" value="<?php echo htmlspecialchars($_POST['mother_phone'] ?? ''); ?>" placeholder="Phone number">
                            </div>
                            <div class="formGroup">
                                <label>Guardian's Name:</label>
                                <input type="text" name="guardian_name" value="<?php echo htmlspecialchars($_POST['guardian_name'] ?? ''); ?>" placeholder="If different from parents">
                            </div>
                            <div class="formGroup">
                                <label>Guardian's Phone:</label>
                                <input type="tel" name="guardian_phone" value="<?php echo htmlspecialchars($_POST['guardian_phone'] ?? ''); ?>" placeholder="Phone number">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="formSection">
                        <h3>Additional Information</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Religion:</label>
                                <input type="text" name="religion" value="<?php echo htmlspecialchars($_POST['religion'] ?? ''); ?>" placeholder="e.g., Buddhist, Christian, Muslim">
                            </div>
                            <div class="formGroup">
                                <label>Ethnicity:</label>
                                <input type="text" name="ethnicity" value="<?php echo htmlspecialchars($_POST['ethnicity'] ?? ''); ?>" placeholder="e.g., Sinhalese, Tamil, Muslim">
                            </div>
                            <div class="formGroup">
                                <label>Birth Certificate No:</label>
                                <input type="text" name="birth_certificate_no" value="<?php echo htmlspecialchars($_POST['birth_certificate_no'] ?? ''); ?>" placeholder="Birth certificate number">
                            </div>
                            <div class="formGroup full-width">
                                <label>Medical Information:</label>
                                <textarea name="medical_info" placeholder="Any medical conditions, allergies, or special needs"><?php echo htmlspecialchars($_POST['medical_info'] ?? ''); ?></textarea>
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