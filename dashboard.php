<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'db_relationships.php';

// Get all dashboard counts
$counts = getDashboardCounts();
// Get recent students and teachers
$recent_students = getRecentStudents(5);
$recent_teachers = getRecentTeachers(5);
// Get teachers per subject data
$subjects_data = getTeachersPerSubject();
$appointment_subjects_data = getTeachersPerAppointmentSubject();

$total_students = $counts['total_students'];
$total_teachers = $counts['total_teachers'];
$total_classes = $counts['total_classes'];
$total_sections = $counts['total_sections'];
$male_teachers = $counts['male_teachers'];
$female_teachers = $counts['female_teachers'];
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - School Management System</title>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li class="hovered">
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
                    <i class="fas fa-bars"></i>
                </div>
                <div class="user">
                    <img src="css/img/DMVLOGO.png" alt="User">
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="sectionHeader">
                <div>
                    <h2>Dashboard Overview</h2>
                    <p style="color: var(--black2); margin-top: 5px; font-size: 14px;">Welcome to School Management System</p>
                </div>
                <div class="headerActions">
                    <div class="stats-summary">
                        <div class="stat-item">
                            <i class="fas fa-school"></i>
                            <span><?php echo $total_sections; ?> Sections</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo $total_teachers + $total_students; ?> Total Users</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="cardBox">
                <div class="card principal-card">
    <div class="card-content">
        <div class="card-info">
            <div class="numbers"><?php echo count(getAllPrincipals()); ?></div>
            <div class="cardName">Principals</div>
            <div class="card-trend">
                <i class="fas fa-user-tie"></i>
                <span>Administrative Staff</span>
            </div>
        </div>
        <div class="card-icon">
            <i class="fas fa-user-tie"></i>
        </div>
    </div>
    <div class="card-footer">
        <a href="principals.php" class="card-link">View Details <i class="fas fa-arrow-right"></i></a>
    </div>
</div>
                <div class="card teacher-card">
                    <div class="card-content">
                        <div class="card-info">
                            <div class="numbers"><?php echo $total_teachers; ?></div>
                            <div class="cardName">Teachers</div>
                            <div class="card-trend">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Teaching Staff</span>
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="teachers.php" class="card-link">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card staff-card">
    <div class="card-content">
        <div class="card-info">
            <div class="numbers"><?php echo count(getAllNonAcademicStaff()); ?></div>
            <div class="cardName">Non-Academic Staff</div>
            <div class="card-trend">
                <i class="fas fa-user-tie"></i>
                <span>Support Staff</span>
            </div>
        </div>
        <div class="card-icon">
            <i class="fas fa-user-tie"></i>
        </div>
    </div>
    <div class="card-footer">
        <a href="non_academic_staff.php" class="card-link">View Details <i class="fas fa-arrow-right"></i></a>
    </div>
</div>
                <div class="card student-card">
                    <div class="card-content">
                        <div class="card-info">
                            <div class="numbers"><?php echo $total_students; ?></div>
                            <div class="cardName">Total Students</div>
                            <div class="card-trend">
                                <i class="fas fa-user-graduate"></i>
                                <span>All Students</span>
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="students.php" class="card-link">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="card class-card">
                    <div class="card-content">
                        <div class="card-info">
                            <div class="numbers"><?php echo $total_classes; ?></div>
                            <div class="cardName">Classes</div>
                            <div class="card-trend">
                                <i class="fas fa-book"></i>
                                <span>Active Classes</span>
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="classes.php" class="card-link">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="card section-card">
                    <div class="card-content">
                        <div class="card-info">
                            <div class="numbers"><?php echo $total_sections; ?></div>
                            <div class="cardName">Sections</div>
                            <div class="card-trend">
                                <i class="fas fa-building"></i>
                                <span>School Sections</span>
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="sections.php" class="card-link">View Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Charts and Data Section -->
            <div class="graphBox">
                <!-- Gender Distribution -->
                <div class="box">
                    <div class="box-header">
                        <h3><i class="fas fa-venus-mars"></i> Teachers by Gender</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="chart-summary">
                        <div class="summary-item">
                            <span class="summary-label">Male</span>
                            <span class="summary-value"><?php echo $male_teachers; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Female</span>
                            <span class="summary-value"><?php echo $female_teachers; ?></span>
                        </div>
                        <div class="summary-item total">
                            <span class="summary-label">Total</span>
                            <span class="summary-value"><?php echo $male_teachers + $female_teachers; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Teachers per Subject -->
                <div class="box">
                    <div class="box-header">
                        <h3><i class="fas fa-book-open"></i> Teachers per Assigned Subject</h3>
                    </div>
                    <div class="table-container" style="max-height: 320px; overflow-y: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th style="text-align: center; width: 100px;">Teachers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($subjects_data)): ?>
                                    <?php 
                                    $total_assignments = 0;
                                    foreach($subjects_data as $subject): 
                                        $total_assignments += $subject['teacher_count'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="subject-info">
                                                <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                <small style="color: var(--black2);"><?php echo htmlspecialchars($subject['subject_code']); ?></small>
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="teacher-count"><?php echo $subject['teacher_count']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center; color: var(--black1); padding: 20px;">
                                            <i class="fas fa-info-circle"></i> No subjects found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(!empty($subjects_data)): ?>
                    <div class="table-footer">
                        <div class="footer-stats">
                            <span class="stat"><?php echo count($subjects_data); ?> Subjects</span>
                            <span class="stat"><?php echo $total_assignments; ?> Assignments</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Teachers per Appointment Subject Section -->
            <div class="graphBox">
                <div class="box">
                    <div class="box-header">
                        <h3><i class="fas fa-user-check"></i> Teachers per Appointment Subject</h3>
                    </div>
                    <div class="table-container" style="max-height: 320px; overflow-y: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Appointment Subject</th>
                                    <th style="text-align: center; width: 100px;">Teachers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($appointment_subjects_data)): ?>
                                    <?php 
                                    $total_appointment_teachers = 0;
                                    foreach($appointment_subjects_data as $subject): 
                                        $total_appointment_teachers += $subject['teacher_count'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="subject-info">
                                                <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                <small style="color: var(--black2);"><?php echo htmlspecialchars($subject['subject_code']); ?></small>
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="teacher-count"><?php echo $subject['teacher_count']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center; color: var(--black1); padding: 20px;">
                                            <i class="fas fa-info-circle"></i> No appointment subject data found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(!empty($appointment_subjects_data)): ?>
                    <div class="table-footer">
                        <div class="footer-stats">
                            <span class="stat"><?php echo count($appointment_subjects_data); ?> Subjects</span>
                            <span class="stat"><?php echo $total_appointment_teachers; ?> Teachers</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="graphBox" style="margin-top: 20px;">
                <div class="box">
                    <div class="box-header">
                        <h3><i class="fas fa-link"></i> Quick Access</h3>
                    </div>
                    <div class="quick-links" style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <a href="students.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                            <i class="fas fa-user-graduate"></i>
                            <span>Manage Students</span>
                        </a>
                        <a href="teachers.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Manage Teachers</span>
                        </a>
                        <a href="classes.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                            <i class="fas fa-book"></i>
                            <span>Manage Classes</span>
                        </a>
                        <a href="non_academic_staff.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                            <i class="fas fa-user-tie"></i>
                            <span>Manage Non-Academic Staff</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const teacherData = {
            male: <?php echo $male_teachers; ?>,
            female: <?php echo $female_teachers; ?>
        };

        // Initialize charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Gender Distribution Chart
            const genderCtx = document.getElementById('genderChart').getContext('2d');
            const genderChart = new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Male Teachers', 'Female Teachers'],
                    datasets: [{
                        data: [teacherData.male, teacherData.female],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });

        // Navigation toggle
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function(){
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }

        // Add hovered class in selected list item
        let list = document.querySelectorAll('.navigation li');
        function activeLink(){
            list.forEach((item) => item.classList.remove('hovered'));
            this.classList.add('hovered');
        }
        list.forEach((item) => item.addEventListener('mouseover', activeLink));
    </script>
</body>
</html>