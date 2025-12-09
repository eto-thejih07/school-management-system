<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

if (!isset($_GET['id'])) {
    header("Location: principals.php?error=Principal ID not provided");
    exit();
}

$principal_id = $_GET['id'];
$principal = getPrincipalDetails($principal_id);
$subjects = getAvailableSubjects();

if (!$principal) {
    header("Location: principals.php?error=Principal not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    try {
        // Collect all form data
        $principal_data = [
            'principal_id' => $_POST['principal_id'],
            'title' => $_POST['title'],
            'job' => $_POST['job'],
            'initials' => $_POST['initials'],
            'principal_name' => $_POST['principal_name'],
            'nic' => $_POST['nic'],
            'dob' => $_POST['dob'],
            'phone' => $_POST['phone'],
            'whatsapp_no' => $_POST['whatsapp_no'],
            'email' => $_POST['email'],
            'priv_address' => $_POST['priv_address'],
            'gender' => $_POST['gender'],
            'date_of_firstappointment' => $_POST['date_of_firstappointment'],
            'first_appointment_subject_id' => $_POST['first_appointment_subject_id'],
            'first_pos' => $_POST['first_pos'],
            'date_of_transfer' => $_POST['date_of_transfer'] ?? null,
            'pos' => $_POST['pos'],
            'doc' => $_POST['doc'] ?? null,
            'edu_q' => $_POST['edu_q'],
            'pro_q' => $_POST['pro_q'],
            'spouse' => $_POST['spouse'] ?? '',
            's_phone' => $_POST['s_phone'] ?? '',
            's_occupation' => $_POST['s_occupation'] ?? '',
            's_work' => $_POST['s_work'] ?? '',
            's_id' => $_POST['s_id'] ?? '',
            's_rel' => $_POST['s_rel'] ?? '',
            'paysheet_no' => $_POST['paysheet_no'],
            'salary_increment_date' => $_POST['salary_increment_date'] ?? null,
            'res' => $_POST['res'] ?? '',
            'skill' => $_POST['skill'] ?? ''
        ];
        
        // Update principal
        $update_result = updatePrincipalDetails($principal_id, $principal_data);
        
        if ($update_result) {
            header("Location: principal_view.php?id=$principal_id&success=Principal updated successfully");
            exit();
        } else {
            $error = "Failed to update principal. Please check the error logs for details.";
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
<title>Edit <?php echo $principal['initials']; ?> - DMV Online</title>
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
                <li class="hovered">
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
                <h2>Edit Principal - <?php echo $principal['initials']; ?></h2>
                <div class="headerActions">
                    <a href="principal_view.php?id=<?php echo $principal_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="principalForm" name="submit" class="btn btn-primary">Update Principal</button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="principalForm" method="POST" class="editForm">
                <div class="formSections">
                    
                    <!-- Personal Details -->
                    <div class="formSection">
                        <h3>Personal Details</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Principal ID:*</label>
                                <input type="text" name="principal_id" value="<?php echo htmlspecialchars($principal['principal_id']); ?>" required readonly>
                            </div>
                            <div class="formGroup">
                                <label>Title:*</label>
                                <select name="title" required>
                                    <option value="">Select Title</option>
                                    <option value="Mr." <?php echo $principal['title'] == 'Mr.' ? 'selected' : ''; ?>>Mr.</option>
                                    <option value="Mrs." <?php echo $principal['title'] == 'Mrs.' ? 'selected' : ''; ?>>Mrs.</option>
                                    <option value="Ms." <?php echo $principal['title'] == 'Ms.' ? 'selected' : ''; ?>>Ms.</option>
                                    <option value="Rev." <?php echo $principal['title'] == 'Rev.' ? 'selected' : ''; ?>>Rev.</option>
                                    <option value="Fr." <?php echo $principal['title'] == 'Fr.' ? 'selected' : ''; ?>>Fr.</option>
                                    <option value="Sr." <?php echo $principal['title'] == 'Sr.' ? 'selected' : ''; ?>>Sr.</option>
                                </select>
                            </div>
                            <div class="formGroup">
                                <label>Name with Initials:*</label>
                                <input type="text" name="initials" value="<?php echo htmlspecialchars($principal['initials']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Full Name:*</label>
                                <input type="text" name="principal_name" value="<?php echo htmlspecialchars($principal['principal_name']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>NIC Number:*</label>
                                <input type="text" name="nic" value="<?php echo htmlspecialchars($principal['nic']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Date of Birth:*</label>
                                <input type="date" name="dob" value="<?php echo htmlspecialchars($principal['dob']); ?>" required>
                            </div>
                            <div class="formGroup">
                                <label>Gender:*</label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $principal['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $principal['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="formGroup full-width">
                                <label>Personal Address:</label>
                                <textarea name="priv_address" rows="3" ><?php echo htmlspecialchars($principal['priv_address']); ?></textarea>
                            </div>
                            <div class="formGroup">
                                <label>Contact Number:</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($principal['phone']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>WhatsApp Number:</label>
                                <input type="text" name="whatsapp_no" value="<?php echo htmlspecialchars($principal['whatsapp_no']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>Email Address:</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($principal['email']); ?>" >
                            </div>
                        </div>
                    </div>

                    <!-- Spouse/Emergency Contact Details -->
                    <div class="formSection">
                        <h3>Spouse/Emergency Contact Details</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Name:</label>
                                <input type="text" name="spouse" value="<?php echo htmlspecialchars($principal['spouse']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Phone:</label>
                                <input type="text" name="s_phone" value="<?php echo htmlspecialchars($principal['s_phone']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Occupation:</label>
                                <input type="text" name="s_occupation" value="<?php echo htmlspecialchars($principal['s_occupation']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Workplace:</label>
                                <input type="text" name="s_work" value="<?php echo htmlspecialchars($principal['s_work']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>NIC:</label>
                                <input type="text" name="s_id" value="<?php echo htmlspecialchars($principal['s_id']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Relationship:</label>
                                <input type="text" name="s_rel" value="<?php echo htmlspecialchars($principal['s_rel']); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Work Details -->
                    <div class="formSection">
                        <h3>Work Details</h3>
                        <div class="formGrid">
                            <div class="formGroup">
                                <label>Occupation:</label>
                                <input type="text" name="job" value="<?php echo htmlspecialchars($principal['job']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>First Position:*</label>
                                <input type="text" name="first_pos" value="<?php echo htmlspecialchars($principal['first_pos']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>Date of First Appointment:</label>
                                <input type="date" name="date_of_firstappointment" value="<?php echo htmlspecialchars($principal['date_of_firstappointment']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>First Appointment Subject:*</label>
                                <select name="first_appointment_subject_id" required>
                                    <option value="">Select Appointment Subject</option>
                                    <?php foreach($subjects as $subject): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>" 
                                            <?php echo ($principal['first_appointment_subject_id'] ?? '') == $subject['subject_id'] ? 'selected' : ''; ?>>
                                            <?php echo $subject['subject_name'] . ' (' . $subject['subject_code'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="formGroup">
                                <label>Date Entered School:</label>
                                <input type="date" name="date_of_transfer" value="<?php echo htmlspecialchars($principal['date_of_transfer']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Current Position:</label>
                                <input type="text" name="pos" value="<?php echo htmlspecialchars($principal['pos']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>Date of Current Position:</label>
                                <input type="date" name="doc" value="<?php echo htmlspecialchars($principal['doc']); ?>">
                            </div>
                            <div class="formGroup">
                                <label>Paysheet Number:</label>
                                <input type="text" name="paysheet_no" value="<?php echo htmlspecialchars($principal['paysheet_no']); ?>" >
                            </div>
                            <div class="formGroup">
                                <label>Salary Increment Date:</label>
                                <input type="date" name="salary_increment_date" value="<?php echo htmlspecialchars($principal['salary_increment_date']); ?>">
                            </div>
                            <div class="formGroup full-width">
                                <label>Educational Qualifications:</label>
                                <textarea name="edu_q" rows="3" ><?php echo htmlspecialchars($principal['edu_q']); ?></textarea>
                            </div>
                            <div class="formGroup full-width">
                                <label>Professional Qualifications:</label>
                                <textarea name="pro_q" rows="3" ><?php echo htmlspecialchars($principal['pro_q']); ?></textarea>
                            </div>
                            <div class="formGroup full-width">
                                <label>Special Skills and Talents:</label>
                                <textarea name="skill" rows="2"><?php echo htmlspecialchars($principal['skill']); ?></textarea>
                            </div>
                            <div class="formGroup full-width">
                                <label>Responsibilities:</label>
                                <textarea name="res" rows="2"><?php echo htmlspecialchars($principal['res']); ?></textarea>
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
        document.getElementById('principalForm').addEventListener('submit', function(e) {
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