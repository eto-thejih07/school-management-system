<?php
require_once 'db.php';

// ========== DASHBOARD FUNCTIONS ==========

/**
 * Get dashboard counts for cards
 */
function getDashboardCounts() {
    global $conn;
    
    $counts = [
        'total_students' => 0,
        'total_teachers' => 0,
        'total_principals' => 0,
        'total_classes' => 0,
        'total_sections' => 0,
        'male_teachers' => 0,
        'female_teachers' => 0
    ];
    
    try {
        // Total students
        $result = $conn->query("SELECT COUNT(*) as total FROM students");
        if ($result) $counts['total_students'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    try {
        // Total teachers
        $result = $conn->query("SELECT COUNT(*) as total FROM teachers");
        if ($result) $counts['total_teachers'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    try {
        // Total principals
        $result = $conn->query("SELECT COUNT(*) as total FROM principals");
        if ($result) $counts['total_principals'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    try {
        // Total classes
        $result = $conn->query("SELECT COUNT(*) as total FROM classes WHERE is_active = 1");
        if ($result) $counts['total_classes'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    try {
        // Total sections
        $result = $conn->query("SELECT COUNT(*) as total FROM sections WHERE is_active = 1");
        if ($result) $counts['total_sections'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Table might not exist, ignore
    }
    
    try {
        // Male teachers
        $result = $conn->query("SELECT COUNT(*) as total FROM teachers WHERE gender = 'Male'");
        if ($result) $counts['male_teachers'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Gender column might not exist, ignore
    }
    
    try {
        // Female teachers
        $result = $conn->query("SELECT COUNT(*) as total FROM teachers WHERE gender = 'Female'");
        if ($result) $counts['female_teachers'] = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        // Gender column might not exist, ignore
    }
    
    return $counts;
}

/**
 * Get recent students
 */
function getRecentStudents($limit = 5) {
    global $conn;
    
    try {
        $query = "SELECT s.id, s.int_name, s.full_name, c.class_name 
                  FROM students s 
                  LEFT JOIN classes c ON s.current_class = c.class_id 
                  ORDER BY s.id DESC 
                  LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get Recent Students Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent teachers
 */
function getRecentTeachers($limit = 5) {
    global $conn;
    
    try {
        $query = "SELECT teacher_id, initials, teacher_name, phone 
                  FROM teachers 
                  ORDER BY teacher_id DESC 
                  LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get Recent Teachers Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get teachers per subject for the chart (assigned subjects)
 */
function getTeachersPerSubject() {
    global $conn;
    
    try {
        $query = "SELECT s.subject_id, s.subject_name, s.subject_code,
                         COUNT(ts.teacher_id) as teacher_count
                  FROM subjects s 
                  LEFT JOIN teacher_subjects ts ON s.subject_id = ts.subject_id AND ts.is_active = 1
                  WHERE s.is_active = 1
                  GROUP BY s.subject_id, s.subject_name, s.subject_code
                  ORDER BY teacher_count DESC, s.subject_name";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get Teachers Per Subject Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get teachers per first appointment subject for the dashboard
 */
function getTeachersPerAppointmentSubject() {
    global $conn;
    
    try {
        $query = "SELECT s.subject_id, s.subject_name, s.subject_code,
                         COUNT(t.teacher_id) as teacher_count
                  FROM subjects s 
                  LEFT JOIN teachers t ON s.subject_id = t.first_appointment_subject_id
                  WHERE s.is_active = 1
                  GROUP BY s.subject_id, s.subject_name, s.subject_code
                  ORDER BY teacher_count DESC, s.subject_name";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get Teachers Per Appointment Subject Error: " . $e->getMessage());
        return [];
    }
}

// ========== STUDENT FUNCTIONS ==========

/**
 * Get all students from the database - ORDERED BY ID
 */
function getAllStudents() {
    global $conn;
    
    try {
        $query = "SELECT s.*, c.class_name, sec.section_name
                  FROM students s 
                  LEFT JOIN classes c ON s.current_class = c.class_id 
                  LEFT JOIN sections sec ON c.section_id = sec.section_id
                  ORDER BY s.id ASC";
        $result = $conn->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Query failed: " . $conn->error);
            return [];
        }
    } catch (Exception $e) {
        error_log("Get All Students Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get student details
 */
function getStudentDetails($student_id) {
    global $conn;
    
    try {
        $query = "SELECT s.*, c.class_name, c.class_id, sec.section_name, sec.section_id
                  FROM students s 
                  LEFT JOIN classes c ON s.current_class = c.class_id 
                  LEFT JOIN sections sec ON c.section_id = sec.section_id
                  WHERE s.id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
        
        return $student;
    } catch (Exception $e) {
        error_log("Get Student Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new student
 */
function addNewStudent($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO students (int_name, full_name, gender, date_of_birth, admission_date) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssss", 
            $data['int_name'],
            $data['full_name'], 
            $data['gender'],
            $data['date_of_birth'],
            $data['admission_date']
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        error_log("addNewStudent Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update student details
 */
function updateStudentDetails($student_id, $data) {
    global $conn;
    
    try {
        $query = "UPDATE students SET 
            int_name = ?, full_name = ?, gender = ?, 
            date_of_birth = ?, admission_date = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssssi", 
            $data['int_name'],
            $data['full_name'],
            $data['gender'],
            $data['date_of_birth'],
            $data['admission_date'],
            $student_id
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        error_log("updateStudentDetails Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a single student
 */
function deleteStudent($student_id) {
    global $conn;
    
    try {
        $query = "DELETE FROM students WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting student: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple students at once
 */
function deleteMultipleStudents($student_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
        
        $query = "DELETE FROM students WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($student_ids)), ...$student_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple students: " . $e->getMessage());
        return false;
    }
}

/**
 * Get students by class
 */
function getStudentsByClass($class_id) {
    global $conn;
    
    try {
        $query = "SELECT s.*, c.class_name, sec.section_name
                  FROM students s 
                  LEFT JOIN classes c ON s.current_class = c.class_id 
                  LEFT JOIN sections sec ON c.section_id = sec.section_id
                  WHERE s.current_class = ? 
                  ORDER BY s.int_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $students;
    } catch (Exception $e) {
        error_log("Get Students By Class Error: " . $e->getMessage());
        return [];
    }
}

// ========== TEACHER FUNCTIONS ==========

/**
 * Get all teachers from the database ordered by teacher_id
 */
function getAllTeachers() {
    global $conn;
    
    try {
        $query = "SELECT t.*, s.subject_name as first_appointment_subject_name 
                  FROM teachers t 
                  LEFT JOIN subjects s ON t.first_appointment_subject_id = s.subject_id 
                  ORDER BY CAST(t.teacher_id AS UNSIGNED), t.teacher_id ASC";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Teachers Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get available teachers for dropdowns - ORDERED BY ID
 */
function getAvailableTeachers() {
    global $conn;
    
    try {
        $query = "SELECT teacher_id, initials, teacher_name FROM teachers ORDER BY CAST(teacher_id AS UNSIGNED), teacher_id ASC";
        $result = $conn->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Query failed: " . $conn->error);
            return [];
        }
    } catch (Exception $e) {
        error_log("Get Available Teachers Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get detailed information for a specific teacher
 */
function getTeacherDetails($teacher_id) {
    global $conn;
    
    try {
        $query = "SELECT t.*, s.subject_name as first_appointment_subject_name 
                  FROM teachers t 
                  LEFT JOIN subjects s ON t.first_appointment_subject_id = s.subject_id 
                  WHERE t.teacher_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $teacher = $result->fetch_assoc();
        $stmt->close();
        
        return $teacher;
    } catch (Exception $e) {
        error_log("Get Teacher Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new teacher to the database
 */
function addNewTeacher($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO teachers (
            teacher_id, title, job, initials, teacher_name, nic, dob, phone, 
            whatsapp_no, email, priv_address, gender, date_of_firstappointment, 
            first_appointment_subject_id, first_pos, date_of_transfer, pos, doc, edu_q, pro_q, 
            spouse, s_phone, s_occupation, s_work, s_id, s_rel, paysheet_no, 
            salary_increment_date, res, skill
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare error: " . $conn->error);
            return false;
        }
        
        // Handle NULL values for optional fields
        $date_of_transfer = !empty($data['date_of_transfer']) ? $data['date_of_transfer'] : null;
        $doc = !empty($data['doc']) ? $data['doc'] : null;
        $salary_increment_date = !empty($data['salary_increment_date']) ? $data['salary_increment_date'] : null;
        $first_appointment_subject_id = !empty($data['first_appointment_subject_id']) ? $data['first_appointment_subject_id'] : null;
        
        $stmt->bind_param("ssssssssssssssssssssssssssssss",
            $data['teacher_id'],
            $data['title'],
            $data['job'],
            $data['initials'],
            $data['teacher_name'],
            $data['nic'],
            $data['dob'],
            $data['phone'],
            $data['whatsapp_no'],
            $data['email'],
            $data['priv_address'],
            $data['gender'],
            $data['date_of_firstappointment'],
            $first_appointment_subject_id,
            $data['first_pos'],
            $date_of_transfer,
            $data['pos'],
            $doc,
            $data['edu_q'],
            $data['pro_q'],
            $data['spouse'],
            $data['s_phone'],
            $data['s_occupation'],
            $data['s_work'],
            $data['s_id'],
            $data['s_rel'],
            $data['paysheet_no'],
            $salary_increment_date,
            $data['res'],
            $data['skill']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error adding teacher: " . $e->getMessage());
        return false;
    }
}

/**
 * Update existing teacher details
 */
function updateTeacherDetails($teacher_id, $data) {
    global $conn;
    
    try {
        // Define all updatable fields
        $fields = [
            'title', 'job', 'initials', 'teacher_name', 'nic', 'dob', 'phone',
            'whatsapp_no', 'email', 'priv_address', 'gender', 'date_of_firstappointment',
            'first_appointment_subject_id', 'first_pos', 'date_of_transfer', 'pos', 'doc', 'edu_q',
            'pro_q', 'spouse', 's_phone', 's_occupation', 's_work', 's_id', 's_rel',
            'paysheet_no', 'salary_increment_date', 'res', 'skill'
        ];
        
        // Build SET part of query
        $setParts = [];
        $values = [];
        $types = '';
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "$field = ?";
                
                // Handle NULL values for date and foreign key fields
                if (in_array($field, ['date_of_transfer', 'doc', 'salary_increment_date', 'first_appointment_subject_id']) && 
                    (empty($data[$field]) || $data[$field] == '')) {
                    $values[] = null;
                } else {
                    $values[] = $data[$field] ?? '';
                }
                
                $types .= 's'; // All fields are treated as strings
            }
        }
        
        // Add teacher_id for WHERE clause
        $values[] = $teacher_id;
        $types .= 's';
        
        // Build final query
        $query = "UPDATE teachers SET " . implode(', ', $setParts) . " WHERE teacher_id = ?";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
            error_log($error);
            return false;
        }
        
        $bind_result = $stmt->bind_param($types, ...$values);
        
        if (!$bind_result) {
            $error = "Bind failed: " . $stmt->error;
            error_log($error);
            return false;
        }
        
        $execute_result = $stmt->execute();
        
        if (!$execute_result) {
            $error = "Execute failed: " . $stmt->error;
            error_log($error);
            return false;
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        $error = "Exception in updateTeacherDetails: " . $e->getMessage();
        error_log($error);
        return false;
    }
}

/**
 * Delete a single teacher
 */
function deleteTeacher($teacher_id) {
    global $conn;
    
    try {
        // First delete related records in teacher_subjects table
        $delete_subjects_query = "DELETE FROM teacher_subjects WHERE teacher_id = ?";
        $delete_subjects_stmt = $conn->prepare($delete_subjects_query);
        $delete_subjects_stmt->bind_param("s", $teacher_id);
        $delete_subjects_stmt->execute();
        $delete_subjects_stmt->close();
        
        // Then delete the teacher
        $query = "DELETE FROM teachers WHERE teacher_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $teacher_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting teacher: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple teachers at once
 */
function deleteMultipleTeachers($teacher_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($teacher_ids) - 1) . '?';
        
        // First delete related records in teacher_subjects table
        $delete_subjects_query = "DELETE FROM teacher_subjects WHERE teacher_id IN ($placeholders)";
        $delete_subjects_stmt = $conn->prepare($delete_subjects_query);
        $delete_subjects_stmt->bind_param(str_repeat('s', count($teacher_ids)), ...$teacher_ids);
        $delete_subjects_stmt->execute();
        $delete_subjects_stmt->close();
        
        // Then delete the teachers
        $query = "DELETE FROM teachers WHERE teacher_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($teacher_ids)), ...$teacher_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple teachers: " . $e->getMessage());
        return false;
    }
}

/**
 * Get subjects assigned to a specific teacher
 */
function getTeacherSubjects($teacher_id) {
    global $conn;
    
    try {
        $query = "SELECT ts.*, s.subject_name, s.subject_code 
                FROM teacher_subjects ts
                INNER JOIN subjects s ON ts.subject_id = s.subject_id
                WHERE ts.teacher_id = ? AND ts.is_active = 1
                ORDER BY ts.is_primary DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $subjects = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $subjects;
    } catch (Exception $e) {
        error_log("Get Teacher Subjects Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Assign subjects to a teacher (up to 4 subjects)
 */
function assignSubjectsToTeacher($teacher_id, $subject_ids) {
    global $conn;
    
    try {
        // First, deactivate all current subjects for this teacher
        $deactivate_query = "UPDATE teacher_subjects SET is_active = 0 WHERE teacher_id = ?";
        $deactivate_stmt = $conn->prepare($deactivate_query);
        $deactivate_stmt->bind_param("s", $teacher_id);
        $deactivate_stmt->execute();
        $deactivate_stmt->close();
        
        if (empty($subject_ids)) {
            return true; // No subjects to assign
        }
        
        // Then insert or update the new subjects
        $query = "INSERT INTO teacher_subjects (teacher_id, subject_id, is_primary, is_active) 
                VALUES (?, ?, ?, 1) 
                ON DUPLICATE KEY UPDATE is_active = 1, is_primary = VALUES(is_primary)";
        
        $stmt = $conn->prepare($query);
        
        foreach ($subject_ids as $index => $subject_id) {
            if (!empty($subject_id)) {
                $is_primary = ($index == 0) ? 1 : 0; // First subject is primary
                $stmt->bind_param("sii", $teacher_id, $subject_id, $is_primary);
                $stmt->execute();
            }
        }
        
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Assign Subjects Error: " . $e->getMessage());
        return false;
    }
}

// ========== PRINCIPAL FUNCTIONS ==========

/**
 * Get all principals from the database ordered by principal_id
 */
function getAllPrincipals() {
    global $conn;
    
    try {
        $query = "SELECT p.*, s.subject_name as first_appointment_subject_name 
                  FROM principals p 
                  LEFT JOIN subjects s ON p.first_appointment_subject_id = s.subject_id 
                  ORDER BY CAST(p.principal_id AS UNSIGNED), p.principal_id ASC";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Principals Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get detailed information for a specific principal
 */
function getPrincipalDetails($principal_id) {
    global $conn;
    
    try {
        $query = "SELECT p.*, s.subject_name as first_appointment_subject_name 
                  FROM principals p 
                  LEFT JOIN subjects s ON p.first_appointment_subject_id = s.subject_id 
                  WHERE p.principal_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $principal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $principal = $result->fetch_assoc();
        $stmt->close();
        
        return $principal;
    } catch (Exception $e) {
        error_log("Get Principal Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new principal to the database
 */
function addNewPrincipal($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO principals (
            principal_id, title, job, initials, principal_name, nic, dob, phone, 
            whatsapp_no, email, priv_address, gender, date_of_firstappointment, 
            first_appointment_subject_id, first_pos, date_of_transfer, pos, doc, edu_q, pro_q, 
            spouse, s_phone, s_occupation, s_work, s_id, s_rel, paysheet_no, 
            salary_increment_date, res, skill
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare error: " . $conn->error);
            return false;
        }
        
        // Handle NULL values for optional fields
        $date_of_transfer = !empty($data['date_of_transfer']) ? $data['date_of_transfer'] : null;
        $doc = !empty($data['doc']) ? $data['doc'] : null;
        $salary_increment_date = !empty($data['salary_increment_date']) ? $data['salary_increment_date'] : null;
        $first_appointment_subject_id = !empty($data['first_appointment_subject_id']) ? $data['first_appointment_subject_id'] : null;
        
        $stmt->bind_param("ssssssssssssssssssssssssssssss",
            $data['principal_id'],
            $data['title'],
            $data['job'],
            $data['initials'],
            $data['principal_name'],
            $data['nic'],
            $data['dob'],
            $data['phone'],
            $data['whatsapp_no'],
            $data['email'],
            $data['priv_address'],
            $data['gender'],
            $data['date_of_firstappointment'],
            $first_appointment_subject_id,
            $data['first_pos'],
            $date_of_transfer,
            $data['pos'],
            $doc,
            $data['edu_q'],
            $data['pro_q'],
            $data['spouse'],
            $data['s_phone'],
            $data['s_occupation'],
            $data['s_work'],
            $data['s_id'],
            $data['s_rel'],
            $data['paysheet_no'],
            $salary_increment_date,
            $data['res'],
            $data['skill']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error adding principal: " . $e->getMessage());
        return false;
    }
}

/**
 * Update existing principal details
 */
function updatePrincipalDetails($principal_id, $data) {
    global $conn;
    
    try {
        // Define all updatable fields
        $fields = [
            'title', 'job', 'initials', 'principal_name', 'nic', 'dob', 'phone',
            'whatsapp_no', 'email', 'priv_address', 'gender', 'date_of_firstappointment',
            'first_appointment_subject_id', 'first_pos', 'date_of_transfer', 'pos', 'doc', 'edu_q',
            'pro_q', 'spouse', 's_phone', 's_occupation', 's_work', 's_id', 's_rel',
            'paysheet_no', 'salary_increment_date', 'res', 'skill'
        ];
        
        // Build SET part of query
        $setParts = [];
        $values = [];
        $types = '';
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "$field = ?";
                
                // Handle NULL values for date and foreign key fields
                if (in_array($field, ['date_of_transfer', 'doc', 'salary_increment_date', 'first_appointment_subject_id']) && 
                    (empty($data[$field]) || $data[$field] == '')) {
                    $values[] = null;
                } else {
                    $values[] = $data[$field] ?? '';
                }
                
                $types .= 's'; // All fields are treated as strings
            }
        }
        
        // Add principal_id for WHERE clause
        $values[] = $principal_id;
        $types .= 's';
        
        // Build final query
        $query = "UPDATE principals SET " . implode(', ', $setParts) . " WHERE principal_id = ?";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
            error_log($error);
            return false;
        }
        
        $bind_result = $stmt->bind_param($types, ...$values);
        
        if (!$bind_result) {
            $error = "Bind failed: " . $stmt->error;
            error_log($error);
            return false;
        }
        
        $execute_result = $stmt->execute();
        
        if (!$execute_result) {
            $error = "Execute failed: " . $stmt->error;
            error_log($error);
            return false;
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        $error = "Exception in updatePrincipalDetails: " . $e->getMessage();
        error_log($error);
        return false;
    }
}

/**
 * Delete a single principal
 */
function deletePrincipal($principal_id) {
    global $conn;
    
    try {
        $query = "DELETE FROM principals WHERE principal_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $principal_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting principal: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple principals at once
 */
function deleteMultiplePrincipals($principal_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($principal_ids) - 1) . '?';
        
        $query = "DELETE FROM principals WHERE principal_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($principal_ids)), ...$principal_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple principals: " . $e->getMessage());
        return false;
    }
}

/**
 * Get principals per first appointment subject for the dashboard
 */
function getPrincipalsPerAppointmentSubject() {
    global $conn;
    
    try {
        $query = "SELECT s.subject_id, s.subject_name, s.subject_code,
                         COUNT(p.principal_id) as principal_count
                  FROM subjects s 
                  LEFT JOIN principals p ON s.subject_id = p.first_appointment_subject_id
                  WHERE s.is_active = 1
                  GROUP BY s.subject_id, s.subject_name, s.subject_code
                  ORDER BY principal_count DESC, s.subject_name";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get Principals Per Appointment Subject Error: " . $e->getMessage());
        return [];
    }
}

// ========== SUBJECT FUNCTIONS ==========

/**
 * Get all subjects from the database ordered by subject_id
 */
function getAllSubjects() {
    global $conn;
    
    try {
        $query = "SELECT s.*, 
                     (SELECT COUNT(*) FROM teacher_subjects WHERE subject_id = s.subject_id AND is_active = 1) as teacher_count
              FROM subjects s 
              ORDER BY s.subject_id ASC";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Subjects Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all available active subjects for dropdowns - ORDERED BY ID
 */
function getAvailableSubjects() {
    global $conn;
    
    try {
        $query = "SELECT subject_id, subject_name, subject_code 
                FROM subjects 
                WHERE is_active = 1 
                ORDER BY subject_id ASC";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get Available Subjects Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get detailed information for a specific subject
 */
function getSubjectDetails($subject_id) {
    global $conn;
    
    try {
        $query = "SELECT s.*,
                     (SELECT COUNT(*) FROM teacher_subjects WHERE subject_id = s.subject_id AND is_active = 1) as teacher_count
              FROM subjects s 
              WHERE s.subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $subject = $result->fetch_assoc();
        $stmt->close();
        
        return $subject;
    } catch (Exception $e) {
        error_log("Get Subject Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new subject to the database
 */
function addNewSubject($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO subjects (subject_name, subject_code, description, is_active) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi",
            $data['subject_name'],
            $data['subject_code'],
            $data['description'],
            $data['is_active']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error adding subject: " . $e->getMessage());
        return false;
    }
}

/**
 * Update existing subject details
 */
function updateSubjectDetails($subject_id, $data) {
    global $conn;
    
    try {
        $query = "UPDATE subjects SET 
            subject_name = ?, subject_code = ?, description = ?, is_active = ?
            WHERE subject_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssii",
            $data['subject_name'],
            $data['subject_code'],
            $data['description'],
            $data['is_active'],
            $subject_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating subject: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a single subject
 */
function deleteSubject($subject_id) {
    global $conn;
    
    try {
        // First, remove from teacher_subjects table
        $delete_query = "DELETE FROM teacher_subjects WHERE subject_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $subject_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Then delete the subject
        $query = "DELETE FROM subjects WHERE subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subject_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting subject: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple subjects at once
 */
function deleteMultipleSubjects($subject_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($subject_ids) - 1) . '?';
        
        // First, remove from teacher_subjects table
        $delete_query = "DELETE FROM teacher_subjects WHERE subject_id IN ($placeholders)";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param(str_repeat('i', count($subject_ids)), ...$subject_ids);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Then delete the subjects
        $query = "DELETE FROM subjects WHERE subject_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($subject_ids)), ...$subject_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple subjects: " . $e->getMessage());
        return false;
    }
}

/**
 * Get teachers assigned to a specific subject
 */
function getSubjectTeachers($subject_id) {
    global $conn;
    
    try {
        $query = "SELECT ts.*, t.teacher_id, t.initials, t.teacher_name, t.phone
                FROM teacher_subjects ts
                INNER JOIN teachers t ON ts.teacher_id = t.teacher_id
                WHERE ts.subject_id = ? AND ts.is_active = 1
                ORDER BY ts.is_primary DESC, t.initials";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $teachers = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $teachers;
    } catch (Exception $e) {
        error_log("Get Subject Teachers Error: " . $e->getMessage());
        return [];
    }
}

// ========== SECTION FUNCTIONS ==========

/**
 * Get all sections from the database - ORDERED BY ID
 */
function getAllSections() {
    global $conn;
    
    try {
        $query = "SELECT s.*, t.initials as section_head_name, t.teacher_id as section_head_id
                  FROM sections s 
                  LEFT JOIN teachers t ON s.section_head_id = t.teacher_id 
                  ORDER BY s.section_id ASC";
        $result = $conn->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Query failed: " . $conn->error);
            return [];
        }
    } catch (Exception $e) {
        error_log("Get All Sections Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get section details
 */
function getSectionDetails($section_id) {
    global $conn;
    
    try {
        $query = "SELECT s.*, t.initials as section_head_name, t.teacher_id, t.phone
                  FROM sections s 
                  LEFT JOIN teachers t ON s.section_head_id = t.teacher_id 
                  WHERE s.section_id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $section = $result->fetch_assoc();
        $stmt->close();
        
        return $section;
    } catch (Exception $e) {
        error_log("Get Section Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new section
 */
function addNewSection($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO sections (section_name, section_head_id, description, is_active) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        // Handle NULL for section_head_id
        $section_head_id = !empty($data['section_head_id']) ? $data['section_head_id'] : NULL;
        
        $stmt->bind_param("sssi",
            $data['section_name'],
            $section_head_id,
            $data['description'],
            $data['is_active']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error adding section: " . $e->getMessage());
        return false;
    }
}

/**
 * Update section details
 */
function updateSectionDetails($section_id, $data) {
    global $conn;
    
    try {
        $query = "UPDATE sections SET 
            section_name = ?, section_head_id = ?, description = ?, is_active = ?
            WHERE section_id = ?";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        // Handle NULL for section_head_id
        $section_head_id = !empty($data['section_head_id']) ? $data['section_head_id'] : NULL;
        
        $stmt->bind_param("sssii",
            $data['section_name'],
            $section_head_id,
            $data['description'],
            $data['is_active'],
            $section_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating section: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a single section
 */
function deleteSection($section_id) {
    global $conn;
    
    try {
        // First check if section has classes
        $check_query = "SELECT COUNT(*) as class_count FROM classes WHERE section_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $section_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $class_count = $result->fetch_assoc()['class_count'];
        $check_stmt->close();
        
        if ($class_count > 0) {
            error_log("Cannot delete section with existing classes");
            return false;
        }
        
        // Delete the section
        $query = "DELETE FROM sections WHERE section_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $section_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting section: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple sections at once
 */
function deleteMultipleSections($section_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($section_ids) - 1) . '?';
        
        // First check if any section has classes
        $check_query = "SELECT COUNT(*) as class_count FROM classes WHERE section_id IN ($placeholders)";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param(str_repeat('i', count($section_ids)), ...$section_ids);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $class_count = $result->fetch_assoc()['class_count'];
        $check_stmt->close();
        
        if ($class_count > 0) {
            error_log("Cannot delete sections with existing classes");
            return false;
        }
        
        // Delete the sections
        $query = "DELETE FROM sections WHERE section_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($section_ids)), ...$section_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple sections: " . $e->getMessage());
        return false;
    }
}

// ========== CLASS FUNCTIONS ==========

/**
 * Get all classes with section and teacher details - ORDERED BY ID
 */
function getAllClasses() {
    global $conn;
    
    try {
        $query = "SELECT c.*, s.section_name, 
                         t.initials as class_teacher_name,
                         (SELECT COUNT(*) FROM students WHERE current_class = c.class_id) as student_count
                  FROM classes c 
                  LEFT JOIN sections s ON c.section_id = s.section_id 
                  LEFT JOIN teachers t ON c.class_teacher_id = t.teacher_id 
                  ORDER BY c.class_id ASC";
        $result = $conn->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Query failed: " . $conn->error);
            return [];
        }
    } catch (Exception $e) {
        error_log("Get All Classes Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get classes in a specific section - ORDERED BY ID
 */
function getClassesBySection($section_id) {
    global $conn;
    
    try {
        $query = "SELECT c.*, t.initials as class_teacher_name,
                         (SELECT COUNT(*) FROM students WHERE current_class = c.class_id) as student_count
                  FROM classes c 
                  LEFT JOIN teachers t ON c.class_teacher_id = t.teacher_id 
                  WHERE c.section_id = ? AND c.is_active = 1
                  ORDER BY c.class_id ASC";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $classes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $classes;
    } catch (Exception $e) {
        error_log("Get Classes By Section Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get class details with section and teacher information
 */
function getClassDetails($class_id) {
    global $conn;
    
    try {
        $query = "SELECT c.*, s.section_name, s.section_head_id,
                         t.initials as class_teacher_name, t.teacher_id, t.phone
                  FROM classes c 
                  LEFT JOIN sections s ON c.section_id = s.section_id 
                  LEFT JOIN teachers t ON c.class_teacher_id = t.teacher_id 
                  WHERE c.class_id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $class = $result->fetch_assoc();
        $stmt->close();
        
        return $class;
    } catch (Exception $e) {
        error_log("Get Class Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new class
 */
function addNewClass($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO classes (class_name, section_id, class_teacher_id, academic_year, is_active) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        // Handle NULL for class_teacher_id
        $class_teacher_id = !empty($data['class_teacher_id']) ? $data['class_teacher_id'] : NULL;
        
        $stmt->bind_param("siiii",
            $data['class_name'],
            $data['section_id'],
            $class_teacher_id,
            $data['academic_year'],
            $data['is_active']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error adding class: " . $e->getMessage());
        return false;
    }
}

/**
 * Update class details
 */
function updateClassDetails($class_id, $data) {
    global $conn;
    
    try {
        $query = "UPDATE classes SET 
            class_name = ?, section_id = ?, class_teacher_id = ?, 
            academic_year = ?, is_active = ?
            WHERE class_id = ?";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }
        
        // Handle NULL for class_teacher_id
        $class_teacher_id = !empty($data['class_teacher_id']) ? $data['class_teacher_id'] : NULL;
        
        $stmt->bind_param("siiiii",
            $data['class_name'],
            $data['section_id'],
            $class_teacher_id,
            $data['academic_year'],
            $data['is_active'],
            $class_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating class: " . $e->getMessage());
        return false;
    }
}

/**
 * Get students in a specific class
 */
function getStudentsInClass($class_id) {
    global $conn;
    
    try {
        $query = "SELECT s.id, s.int_name, s.full_name, s.gender, s.date_of_birth, s.admission_date
                  FROM students s 
                  WHERE s.current_class = ? 
                  ORDER BY s.int_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $students;
    } catch (Exception $e) {
        error_log("Get Students In Class Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete a single class
 */
function deleteClass($class_id) {
    global $conn;
    
    try {
        $query = "DELETE FROM classes WHERE class_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting class: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple classes at once
 */
function deleteMultipleClasses($class_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($class_ids) - 1) . '?';
        
        $query = "DELETE FROM classes WHERE class_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($class_ids)), ...$class_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple classes: " . $e->getMessage());
        return false;
    }
}
// ========== NON-ACADEMIC STAFF FUNCTIONS ==========

/**
 * Get all non-academic staff from the database ordered by staff_id
 */
function getAllNonAcademicStaff() {
    global $conn;
    
    try {
        $query = "SELECT * FROM non_academic_staff 
                  ORDER BY CAST(staff_id AS UNSIGNED), staff_id ASC";
        $result = $conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Get All Non-Academic Staff Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get detailed information for a specific non-academic staff member
 */
function getNonAcademicStaffDetails($staff_id) {
    global $conn;
    
    try {
        $query = "SELECT * FROM non_academic_staff WHERE staff_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();
        $stmt->close();
        
        return $staff;
    } catch (Exception $e) {
        error_log("Get Non-Academic Staff Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new non-academic staff member to the database
 */
function addNewNonAcademicStaff($data) {
    global $conn;
    
    try {
        $query = "INSERT INTO non_academic_staff (
            staff_id, title, job, initials, staff_name, nic, dob, phone, 
            whatsapp_no, email, priv_address, gender, date_of_firstappointment, 
            first_pos, date_of_transfer, pos, doc, edu_q, pro_q, 
            spouse, s_phone, s_occupation, s_work, s_id, s_rel, paysheet_no, 
            salary_increment_date, res, skill
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare error: " . $conn->error);
            return false;
        }
        
        // Handle NULL values for optional fields
        $date_of_transfer = !empty($data['date_of_transfer']) ? $data['date_of_transfer'] : null;
        $doc = !empty($data['doc']) ? $data['doc'] : null;
        $salary_increment_date = !empty($data['salary_increment_date']) ? $data['salary_increment_date'] : null;
        
        $stmt->bind_param("sssssssssssssssssssssssssssss",
            $data['staff_id'],
            $data['title'],
            $data['job'],
            $data['initials'],
            $data['staff_name'],
            $data['nic'],
            $data['dob'],
            $data['phone'],
            $data['whatsapp_no'],
            $data['email'],
            $data['priv_address'],
            $data['gender'],
            $data['date_of_firstappointment'],
            $data['first_pos'],
            $date_of_transfer,
            $data['pos'],
            $doc,
            $data['edu_q'],
            $data['pro_q'],
            $data['spouse'],
            $data['s_phone'],
            $data['s_occupation'],
            $data['s_work'],
            $data['s_id'],
            $data['s_rel'],
            $data['paysheet_no'],
            $salary_increment_date,
            $data['res'],
            $data['skill']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error adding non-academic staff: " . $e->getMessage());
        return false;
    }
}

/**
 * Update existing non-academic staff details
 */
function updateNonAcademicStaffDetails($staff_id, $data) {
    global $conn;
    
    try {
        // Define all updatable fields
        $fields = [
            'title', 'job', 'initials', 'staff_name', 'nic', 'dob', 'phone',
            'whatsapp_no', 'email', 'priv_address', 'gender', 'date_of_firstappointment',
            'first_pos', 'date_of_transfer', 'pos', 'doc', 'edu_q', 'pro_q',
            'spouse', 's_phone', 's_occupation', 's_work', 's_id', 's_rel',
            'paysheet_no', 'salary_increment_date', 'res', 'skill'
        ];
        
        // Build SET part of query
        $setParts = [];
        $values = [];
        $types = '';
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "$field = ?";
                
                // Handle NULL values for date fields
                if (in_array($field, ['date_of_transfer', 'doc', 'salary_increment_date']) && 
                    (empty($data[$field]) || $data[$field] == '')) {
                    $values[] = null;
                } else {
                    $values[] = $data[$field] ?? '';
                }
                
                $types .= 's'; // All fields are treated as strings
            }
        }
        
        // Add staff_id for WHERE clause
        $values[] = $staff_id;
        $types .= 's';
        
        // Build final query
        $query = "UPDATE non_academic_staff SET " . implode(', ', $setParts) . " WHERE staff_id = ?";
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
            error_log($error);
            return false;
        }
        
        $bind_result = $stmt->bind_param($types, ...$values);
        
        if (!$bind_result) {
            $error = "Bind failed: " . $stmt->error;
            error_log($error);
            return false;
        }
        
        $execute_result = $stmt->execute();
        
        if (!$execute_result) {
            $error = "Execute failed: " . $stmt->error;
            error_log($error);
            return false;
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        $error = "Exception in updateNonAcademicStaffDetails: " . $e->getMessage();
        error_log($error);
        return false;
    }
}

/**
 * Delete a single non-academic staff member
 */
function deleteNonAcademicStaff($staff_id) {
    global $conn;
    
    try {
        $query = "DELETE FROM non_academic_staff WHERE staff_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $staff_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting non-academic staff: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete multiple non-academic staff at once
 */
function deleteMultipleNonAcademicStaff($staff_ids) {
    global $conn;
    
    try {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($staff_ids) - 1) . '?';
        
        $query = "DELETE FROM non_academic_staff WHERE staff_id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($staff_ids)), ...$staff_ids);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error deleting multiple non-academic staff: " . $e->getMessage());
        return false;
    }
}
?>