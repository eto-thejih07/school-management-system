<?php
session_start();
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

if (!$principal) {
    header("Location: principals.php?error=Principal not found");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $principal['initials']; ?> - DMV Online</title>
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
                <h2>Principal Details - <?php echo $principal['initials']; ?></h2>
                <div class="headerActions">
                    <a href="principals.php" class="btn btn-secondary">Back to List</a>
                    <a href="principal_edit.php?id=<?php echo $principal_id; ?>" class="btn btn-primary">Edit Principal</a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo $principal_id; ?>')">Delete Principal</button>
                </div>
            </div>

            <div class="content">
                <div class="detail-sections">
                    <!-- Personal Details -->
                    <div class="detail-section">
                        <h3>Personal Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Principal ID:</label>
                                <span><?php echo htmlspecialchars($principal['principal_id']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Title:</label>
                                <span><?php echo htmlspecialchars($principal['title']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Name with Initials:</label>
                                <span><?php echo htmlspecialchars($principal['initials']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Full Name:</label>
                                <span><?php echo htmlspecialchars($principal['principal_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>NIC Number:</label>
                                <span><?php echo htmlspecialchars($principal['nic']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Date of Birth:</label>
                                <span><?php echo htmlspecialchars($principal['dob']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Gender:</label>
                                <span><?php echo htmlspecialchars($principal['gender']); ?></span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Personal Address:</label>
                                <span><?php echo htmlspecialchars($principal['priv_address']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Contact Number:</label>
                                <span><?php echo htmlspecialchars($principal['phone']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>WhatsApp Number:</label>
                                <span><?php echo htmlspecialchars($principal['whatsapp_no']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Email Address:</label>
                                <span><?php echo htmlspecialchars($principal['email']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Spouse/Emergency Contact Details -->
                    <?php if($principal['spouse'] || $principal['s_phone']): ?>
                    <div class="detail-section">
                        <h3>Spouse/Emergency Contact Details</h3>
                        <div class="detail-grid">
                            <?php if($principal['spouse']): ?>
                            <div class="detail-item">
                                <label>Name:</label>
                                <span><?php echo htmlspecialchars($principal['spouse']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($principal['s_phone']): ?>
                            <div class="detail-item">
                                <label>Phone:</label>
                                <span><?php echo htmlspecialchars($principal['s_phone']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($principal['s_occupation']): ?>
                            <div class="detail-item">
                                <label>Occupation:</label>
                                <span><?php echo htmlspecialchars($principal['s_occupation']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($principal['s_work']): ?>
                            <div class="detail-item">
                                <label>Workplace:</label>
                                <span><?php echo htmlspecialchars($principal['s_work']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($principal['s_id']): ?>
                            <div class="detail-item">
                                <label>NIC:</label>
                                <span><?php echo htmlspecialchars($principal['s_id']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($principal['s_rel']): ?>
                            <div class="detail-item">
                                <label>Relationship:</label>
                                <span><?php echo htmlspecialchars($principal['s_rel']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Work Details -->
                    <div class="detail-section">
                        <h3>Work Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Occupation:</label>
                                <span><?php echo htmlspecialchars($principal['job']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>First Position:</label>
                                <span><?php echo htmlspecialchars($principal['first_pos']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Date of First Appointment:</label>
                                <span><?php echo htmlspecialchars($principal['date_of_firstappointment']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>First Appointment Subject:</label>
                                <span>
                                    <?php 
                                    if (!empty($principal['first_appointment_subject_name'])) {
                                        echo htmlspecialchars($principal['first_appointment_subject_name']);
                                    } else {
                                        echo '<em style="color: var(--black2);">Not specified</em>';
                                    }
                                    ?>
                                </span>
                            </div>
                            <?php if($principal['date_of_transfer']): ?>
                            <div class="detail-item">
                                <label>Date Entered School:</label>
                                <span><?php echo htmlspecialchars($principal['date_of_transfer']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <label>Current Position:</label>
                                <span><?php echo htmlspecialchars($principal['pos']); ?></span>
                            </div>
                            <?php if($principal['doc']): ?>
                            <div class="detail-item">
                                <label>Date of Current Position:</label>
                                <span><?php echo htmlspecialchars($principal['doc']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <label>Paysheet Number:</label>
                                <span><?php echo htmlspecialchars($principal['paysheet_no']); ?></span>
                            </div>
                            <?php if($principal['salary_increment_date']): ?>
                            <div class="detail-item">
                                <label>Salary Increment Date:</label>
                                <span><?php echo htmlspecialchars($principal['salary_increment_date']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item full-width">
                                <label>Educational Qualifications:</label>
                                <span><?php echo nl2br(htmlspecialchars($principal['edu_q'])); ?></span>
                            </div>
                            <div class="detail-item full-width">
                                <label>Professional Qualifications:</label>
                                <span><?php echo nl2br(htmlspecialchars($principal['pro_q'])); ?></span>
                            </div>
                            <?php if($principal['skill']): ?>
                            <div class="detail-item full-width">
                                <label>Special Skills and Talents:</label>
                                <span><?php echo nl2br(htmlspecialchars($principal['skill'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($principal['res']): ?>
                            <div class="detail-item full-width">
                                <label>Responsibilities:</label>
                                <span><?php echo nl2br(htmlspecialchars($principal['res'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
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

        // Confirm and delete principal
        function confirmDelete(principalId) {
            if (confirm('Are you sure you want to delete this principal? This action cannot be undone.')) {
                window.location.href = 'principal_delete.php?id=' + principalId;
            }
        }
    </script>
</body>
</html>