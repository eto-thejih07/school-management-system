<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// Create tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    color VARCHAR(20) DEFAULT '#3498db',
    icon VARCHAR(50) DEFAULT 'fas fa-building',
    built_date DATE NULL,
    roof_material VARCHAR(100) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS building_repairs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    repair_date DATE NOT NULL,
    description TEXT NOT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00,
    contractor VARCHAR(200) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    floor_number INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    type VARCHAR(50) DEFAULT 'classroom',
    position_index INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE
)");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_building'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $color = $conn->real_escape_string($_POST['color'] ?? '#3498db');
        $icon = $conn->real_escape_string($_POST['icon'] ?? 'fas fa-building');
        $built_date = !empty($_POST['built_date']) ? $conn->real_escape_string($_POST['built_date']) : NULL;
        $roof_material = $conn->real_escape_string($_POST['roof_material'] ?? '');
        
        $sql = "INSERT INTO buildings (name, description, color, icon, built_date, roof_material) 
                VALUES ('$name', '$description', '$color', '$icon', " . 
                ($built_date ? "'$built_date'" : "NULL") . ", '$roof_material')";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Building added successfully!";
        }
    }
    elseif (isset($_POST['update_building_info'])) {
        $building_id = intval($_POST['building_id']);
        $built_date = !empty($_POST['built_date']) ? $conn->real_escape_string($_POST['built_date']) : NULL;
        $roof_material = $conn->real_escape_string($_POST['roof_material'] ?? '');
        
        $sql = "UPDATE buildings SET 
                built_date = " . ($built_date ? "'$built_date'" : "NULL") . ",
                roof_material = '$roof_material'
                WHERE id = $building_id";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Building information updated successfully!";
        }
    }
    elseif (isset($_POST['add_repair'])) {
        $building_id = intval($_POST['building_id']);
        $repair_date = $conn->real_escape_string($_POST['repair_date']);
        $description = $conn->real_escape_string($_POST['description']);
        $cost = floatval($_POST['cost'] ?? 0);
        $contractor = $conn->real_escape_string($_POST['contractor'] ?? '');
        
        $sql = "INSERT INTO building_repairs (building_id, repair_date, description, cost, contractor) 
                VALUES ($building_id, '$repair_date', '$description', $cost, '$contractor')";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Repair record added successfully!";
        }
    }
    elseif (isset($_POST['delete_repair'])) {
        $repair_id = intval($_POST['repair_id']);
        $conn->query("DELETE FROM building_repairs WHERE id = $repair_id");
        $_SESSION['message'] = "Repair record deleted!";
    }
    elseif (isset($_POST['add_floor'])) {
        $building_id = intval($_POST['building_id']);
        $floor_number = intval($_POST['floor_number']);
        $name = $conn->real_escape_string($_POST['name']);
        
        $sql = "INSERT INTO floors (building_id, floor_number, name) VALUES ($building_id, $floor_number, '$name')";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Floor added successfully!";
        }
    }
    elseif (isset($_POST['add_multiple_floors'])) {
        $building_id = intval($_POST['building_id']);
        $num_floors = intval($_POST['num_floors']);
        
        // Get the highest existing floor number for this building
        $result = $conn->query("SELECT MAX(floor_number) as max_floor FROM floors WHERE building_id = $building_id");
        $max_floor = $result->fetch_assoc()['max_floor'] ?? 0;
        
        $start_floor = $max_floor + 1;
        
        for ($i = 0; $i < $num_floors; $i++) {
            $floor_number = $start_floor + $i;
            $name = "Floor " . $floor_number; // Default name
            
            $sql = "INSERT INTO floors (building_id, floor_number, name) VALUES ($building_id, $floor_number, '$name')";
            $conn->query($sql);
        }
        
        $_SESSION['message'] = "$num_floors floors added successfully!";
    }
    elseif (isset($_POST['update_floor_name'])) {
        $floor_id = intval($_POST['floor_id']);
        $name = $conn->real_escape_string($_POST['name']);
        
        $sql = "UPDATE floors SET name = '$name' WHERE id = $floor_id";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Floor name updated successfully!";
        }
    }
    elseif (isset($_POST['add_room'])) {
        $floor_id = intval($_POST['floor_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $type = $conn->real_escape_string($_POST['type'] ?? 'classroom');
        
        $sql = "INSERT INTO rooms (floor_id, name, type, position_index) VALUES ($floor_id, '$name', '$type', 1)";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Room added successfully!";
        }
    }
    elseif (isset($_POST['delete_floor'])) {
        $floor_id = intval($_POST['floor_id']);
        $conn->query("DELETE FROM floors WHERE id = $floor_id");
        $_SESSION['message'] = "Floor deleted!";
    }
    elseif (isset($_POST['delete_room'])) {
        $room_id = intval($_POST['room_id']);
        $conn->query("DELETE FROM rooms WHERE id = $room_id");
        $_SESSION['message'] = "Room deleted!";
    }
    
    header("Location: buildings.php" . (isset($_POST['building_id']) ? "?building_id=" . $_POST['building_id'] : ""));
    exit();
}

// Get data
$buildings = $conn->query("SELECT * FROM buildings ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$current_building_id = $_GET['building_id'] ?? ($buildings[0]['id'] ?? null);
$current_building = $current_building_id ? $conn->query("SELECT * FROM buildings WHERE id = $current_building_id")->fetch_assoc() : null;

// Get repair records for current building
$repairs = [];
if ($current_building_id) {
    $repairs = $conn->query("
        SELECT * FROM building_repairs 
        WHERE building_id = $current_building_id 
        ORDER BY repair_date DESC
    ")->fetch_all(MYSQLI_ASSOC);
}

$floors = [];
if ($current_building_id) {
    // Get floors for the current building
    $floors_result = $conn->query("
        SELECT f.*, 
               (SELECT COUNT(*) FROM rooms WHERE floor_id = f.id) as room_count 
        FROM floors f 
        WHERE f.building_id = $current_building_id 
        ORDER BY f.floor_number DESC
    ");
    
    if ($floors_result) {
        $floors = $floors_result->fetch_all(MYSQLI_ASSOC);
        
        // Get rooms for each floor separately to avoid reference issues
        foreach ($floors as $index => $floor) {
            $floor_id = $floor['id'];
            $rooms_result = $conn->query("SELECT * FROM rooms WHERE floor_id = $floor_id ORDER BY name");
            if ($rooms_result) {
                $floors[$index]['rooms'] = $rooms_result->fetch_all(MYSQLI_ASSOC);
            } else {
                $floors[$index]['rooms'] = [];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Building Management</title>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.building-management { padding: 20px; }
.building-container { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
.building-sidebar, .building-main { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.building-item { padding: 15px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 10px; cursor: pointer; }
.building-item.active { border-color: #5c0a0a; background: #f9f9f9; }
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
.modal-content { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px; margin: 50px auto; }
.floor-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #ddd; }
.floor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 10px; }
.room-item { background: white; padding: 10px; border-radius: 6px; border: 1px solid #ddd; }
.form-group { margin: 15px 0; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
.tab-container { margin: 20px 0; }
.tab-buttons { display: flex; border-bottom: 2px solid #ddd; }
.tab-button { padding: 10px 20px; background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; }
.tab-button.active { border-bottom-color: #5c0a0a; color: #5c0a0a; font-weight: bold; }
.tab-content { display: none; padding: 20px 0; }
.tab-content.active { display: block; }
.repair-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #5c0a0a; }
.repair-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.info-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
.editable-field { cursor: pointer; padding: 5px; border-radius: 4px; transition: background-color 0.2s; }
.editable-field:hover { background-color: #e9ecef; }
</style>
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
                <li class="hovered"><a href="buildings.php"><span class="icon">🏫</span><span class="title">Buildings</span></a></li>
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
                <div class="toggle"><i class="fas fa-bars"></i></div>
                <div class="user"><img src="css/img/DMVLOGO.png" alt="User"></div>
            </div>

            <div class="building-management">
                <div class="sectionHeader">
                    <div><h2>Building Management</h2></div>
                    <button class="btn btn-primary" onclick="showModal('buildingModal')">Add Building</button>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>

                <div class="building-container">
                    <div class="building-sidebar">
                        <h3>Buildings</h3>
                        <?php foreach ($buildings as $building): ?>
                            <div class="building-item <?= $building['id'] == $current_building_id ? 'active' : '' ?>" 
                                 onclick="window.location='buildings.php?building_id=<?= $building['id'] ?>'">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:30px; height:30px; background:<?= $building['color'] ?>; border-radius:6px; display:flex; align-items:center; justify-content:center; color:white;">
                                        <i class="<?= $building['icon'] ?>"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($building['name']) ?></strong><br>
                                        <small><?= htmlspecialchars($building['description']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="building-main">
                        <?php if ($current_building): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3>
                                    <i class="<?= $current_building['icon'] ?>" style="color: <?= $current_building['color'] ?>;"></i>
                                    <?= htmlspecialchars($current_building['name']) ?>
                                </h3>
                                <div>
                                    <button class="btn btn-primary" onclick="showModal('floorModal')">Add Floors</button>
                                </div>
                            </div>

                            <div class="tab-container">
                                <div class="tab-buttons">
                                    <button class="tab-button active" onclick="showTab('floors-tab')">Floors (<?= count($floors) ?>)</button>
                                    <button class="tab-button" onclick="showTab('building-info-tab')">Building Info</button>
                                </div>

                                <div id="floors-tab" class="tab-content active">
                                    <?php if (empty($floors)): ?>
                                        <div style="text-align: center; padding: 40px; color: #666;">
                                            <i class="fas fa-layer-group" style="font-size: 3em; margin-bottom: 15px; opacity: 0.5;"></i>
                                            <h4>No Floors Added</h4>
                                            <p>Add floors to start organizing rooms in this building.</p>
                                            <button class="btn btn-primary" onclick="showModal('floorModal')">Add Your First Floor</button>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($floors as $floor): ?>
                                            <div class="floor-item">
                                                <div class="floor-header">
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        <h4 style="margin: 0;">
                                                            <i class="fas fa-layer-group" style="color: #5c0a0a;"></i>
                                                            <span id="floor-name-<?= $floor['id'] ?>"><?= htmlspecialchars($floor['name']) ?></span>
                                                        </h4>
                                                        <span class="badge">Floor <?= $floor['floor_number'] ?></span>
                                                        <span class="badge"><?= $floor['room_count'] ?> rooms</span>
                                                    </div>
                                                    <div style="display: flex; gap: 5px;">
                                                        <button class="btn btn-sm btn-outline" onclick="editFloorName(<?= $floor['id'] ?>, '<?= htmlspecialchars($floor['name']) ?>')">
                                                            <i class="fas fa-edit"></i> Rename
                                                        </button>
                                                        <button class="btn btn-sm btn-primary" onclick="showRoomModal(<?= $floor['id'] ?>)">
                                                            <i class="fas fa-plus"></i> Add Room
                                                        </button>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="delete_floor" value="1">
                                                            <input type="hidden" name="floor_id" value="<?= $floor['id'] ?>">
                                                            <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this floor and all its rooms?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <?php if (!empty($floor['rooms'])): ?>
                                                    <div class="room-grid">
                                                        <?php foreach ($floor['rooms'] as $room): ?>
                                                            <div class="room-item">
                                                                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                                                    <div>
                                                                        <strong><?= htmlspecialchars($room['name']) ?></strong>
                                                                        <div style="font-size:0.8em; color:#666; margin-top:2px;"><?= ucfirst($room['type']) ?></div>
                                                                    </div>
                                                                    <span style="background:#5c0a0a; color:white; padding:2px 8px; border-radius:10px; font-size:0.7em;">
                                                                        <?= $room['type'] ?>
                                                                    </span>
                                                                </div>
                                                                <form method="POST" style="margin-top:8px;">
                                                                    <input type="hidden" name="delete_room" value="1">
                                                                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                                    <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                                                                    <button type="submit" class="btn-action delete sm" onclick="return confirm('Delete this room?')">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div style="text-align: center; padding: 20px; color: #999;">
                                                        <i class="fas fa-door-open" style="font-size: 2em; margin-bottom: 10px; opacity: 0.5;"></i>
                                                        <p>No rooms added to this floor yet.</p>
                                                        <button class="btn btn-sm btn-primary" onclick="showRoomModal(<?= $floor['id'] ?>)">
                                                            <i class="fas fa-plus"></i> Add First Room
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div id="building-info-tab" class="tab-content">
                                    <div class="info-grid">
                                        <div class="info-card">
                                            <h4 style="margin-top: 0; color: #5c0a0a;">Basic Information</h4>
                                            <div style="display: grid; gap: 15px;">
                                                <div>
                                                    <label><strong>Building Name:</strong></label>
                                                    <p><?= htmlspecialchars($current_building['name']) ?></p>
                                                </div>
                                                <div>
                                                    <label><strong>Description:</strong></label>
                                                    <p><?= htmlspecialchars($current_building['description']) ?: 'No description provided' ?></p>
                                                </div>
                                                <div>
                                                    <label><strong>Total Floors:</strong></label>
                                                    <p><?= count($floors) ?></p>
                                                </div>
                                                <div>
                                                    <label><strong>Total Rooms:</strong></label>
                                                    <p><?= array_sum(array_column($floors, 'room_count')) ?></p>
                                                </div>
                                                <div>
                                                    <label><strong>Created:</strong></label>
                                                    <p><?= date('F j, Y', strtotime($current_building['created_at'])) ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="info-card">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                                <h4 style="margin: 0; color: #5c0a0a;">Construction Details</h4>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="showModal('buildingInfoModal')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                            <div style="display: grid; gap: 15px;">
                                                <div>
                                                    <label><strong>Date Built:</strong></label>
                                                    <p class="editable-field" onclick="showModal('buildingInfoModal')">
                                                        <?= $current_building['built_date'] ? date('F j, Y', strtotime($current_building['built_date'])) : 'Not specified' ?>
                                                    </p>
                                                </div>
                                                <div>
                                                    <label><strong>Roof Material:</strong></label>
                                                    <p class="editable-field" onclick="showModal('buildingInfoModal')">
                                                        <?= htmlspecialchars($current_building['roof_material']) ?: 'Not specified' ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-top: 30px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                            <h4 style="color: #5c0a0a;">Repairs & Maintenance History</h4>
                                            <button class="btn btn-primary" onclick="showModal('repairModal')">
                                                <i class="fas fa-plus"></i> Add Repair Record
                                            </button>
                                        </div>

                                        <?php if (empty($repairs)): ?>
                                            <div style="text-align: center; padding: 40px; color: #666;">
                                                <i class="fas fa-tools" style="font-size: 3em; margin-bottom: 15px; opacity: 0.5;"></i>
                                                <h4>No Repair Records</h4>
                                                <p>Add repair records to track maintenance history for this building.</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($repairs as $repair): ?>
                                                <div class="repair-item">
                                                    <div class="repair-header">
                                                        <div>
                                                            <strong style="color: #5c0a0a;"><?= date('F j, Y', strtotime($repair['repair_date'])) ?></strong>
                                                            <?php if (!empty($repair['contractor'])): ?>
                                                                <span style="margin-left: 10px; color: #666;">by <?= htmlspecialchars($repair['contractor']) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="display: flex; gap: 5px; align-items: center;">
                                                            <?php if ($repair['cost'] > 0): ?>
                                                                <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8em;">
                                                                    $<?= number_format($repair['cost'], 2) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="delete_repair" value="1">
                                                                <input type="hidden" name="repair_id" value="<?= $repair['id'] ?>">
                                                                <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                                                                <button type="submit" class="btn-action delete sm" onclick="return confirm('Delete this repair record?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <p style="margin: 0;"><?= htmlspecialchars($repair['description']) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="text-align:center; padding:40px;">
                                <i class="fas fa-building" style="font-size: 4em; color: #ddd; margin-bottom: 20px;"></i>
                                <h3>No Buildings Found</h3>
                                <p>Add your first building to get started with floor and room management.</p>
                                <button class="btn btn-primary" onclick="showModal('buildingModal')">
                                    <i class="fas fa-plus"></i> Add Your First Building
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Building Modal -->
    <div class="modal" id="buildingModal">
        <div class="modal-content">
            <h3>Add New Building</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Building Name *</label>
                    <input type="text" name="name" placeholder="Enter building name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Enter building description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Building Color</label>
                    <input type="color" name="color" value="#3498db" style="width: 100%; height: 40px;">
                </div>
                <div class="form-group">
                    <label>Building Icon</label>
                    <select name="icon" style="width: 100%; padding: 10px;">
                        <option value="fas fa-school">School</option>
                        <option value="fas fa-book">Library</option>
                        <option value="fas fa-flask">Science Lab</option>
                        <option value="fas fa-basketball-ball">Gym</option>
                        <option value="fas fa-utensils">Cafeteria</option>
                        <option value="fas fa-building">Office Building</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date Built</label>
                    <input type="date" name="built_date">
                </div>
                <div class="form-group">
                    <label>Roof Material</label>
                    <input type="text" name="roof_material" placeholder="e.g., Concrete, Metal, Tile, Shingle">
                </div>
                <input type="hidden" name="add_building" value="1">
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('buildingModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Building</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Building Info Edit Modal -->
    <div class="modal" id="buildingInfoModal">
        <div class="modal-content">
            <h3>Edit Building Information</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Date Built</label>
                    <input type="date" name="built_date" value="<?= $current_building['built_date'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Roof Material</label>
                    <input type="text" name="roof_material" placeholder="e.g., Concrete, Metal, Tile, Shingle" 
                           value="<?= htmlspecialchars($current_building['roof_material'] ?? '') ?>">
                </div>
                <input type="hidden" name="update_building_info" value="1">
                <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('buildingInfoModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Information</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Repair Modal -->
    <div class="modal" id="repairModal">
        <div class="modal-content">
            <h3>Add Repair Record</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Repair Date *</label>
                    <input type="date" name="repair_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" placeholder="Describe the repair work done..." rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Cost ($)</label>
                    <input type="number" name="cost" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Contractor</label>
                    <input type="text" name="contractor" placeholder="Contractor name (if any)">
                </div>
                <input type="hidden" name="add_repair" value="1">
                <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('repairModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Floor Modal -->
    <div class="modal" id="floorModal">
        <div class="modal-content">
            <h3>Add Floors</h3>
            
            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="showFloorTab('single-floor-tab')">Single Floor</button>
                    <button class="tab-button" onclick="showFloorTab('multiple-floors-tab')">Multiple Floors</button>
                </div>

                <!-- Single Floor Tab -->
                <div id="single-floor-tab" class="tab-content active">
                    <form method="POST">
                        <div class="form-group">
                            <label>Floor Number *</label>
                            <input type="number" name="floor_number" min="1" value="<?= (($floors[0]['floor_number'] ?? 0) + 1) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Floor Name *</label>
                            <input type="text" name="name" placeholder="e.g., Ground Floor, First Floor, Basement" required>
                        </div>
                        <input type="hidden" name="add_floor" value="1">
                        <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                        <div style="display:flex; gap:10px; justify-content:flex-end;">
                            <button type="button" class="btn btn-secondary" onclick="hideModal('floorModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Floor</button>
                        </div>
                    </form>
                </div>

                <!-- Multiple Floors Tab -->
                <div id="multiple-floors-tab" class="tab-content">
                    <form method="POST">
                        <div class="form-group">
                            <label>Number of Floors to Add *</label>
                            <input type="number" name="num_floors" min="1" max="20" value="1" required>
                            <small style="color: #666;">Floors will be automatically numbered starting from the next available number</small>
                        </div>
                        <input type="hidden" name="add_multiple_floors" value="1">
                        <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                        <div style="display:flex; gap:10px; justify-content:flex-end;">
                            <button type="button" class="btn btn-secondary" onclick="hideModal('floorModal')">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Floors</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Floor Name Modal -->
    <div class="modal" id="editFloorModal">
        <div class="modal-content">
            <h3>Rename Floor</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Floor Name *</label>
                    <input type="text" id="editFloorName" name="name" required>
                </div>
                <input type="hidden" name="update_floor_name" value="1">
                <input type="hidden" id="editFloorId" name="floor_id">
                <input type="hidden" name="building_id" value="<?= $current_building_id ?>">
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('editFloorModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Name</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Room Modal -->
    <div class="modal" id="roomModal">
        <div class="modal-content">
            <h3>Add New Room</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Room Name *</label>
                    <input type="text" name="name" placeholder="e.g., Room 101, Chemistry Lab, Principal's Office" required>
                </div>
                <div class="form-group">
                    <label>Room Type *</label>
                    <select name="type" required>
                        <option value="classroom">Classroom</option>
                        <option value="lab">Laboratory</option>
                        <option value="office">Office</option>
                        <option value="library">Library</option>
                        <option value="gym">Gymnasium</option>
                        <option value="cafeteria">Cafeteria</option>
                        <option value="auditorium">Auditorium</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <input type="hidden" name="add_room" value="1">
                <input type="hidden" name="floor_id" id="roomFloorId">
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('roomModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Room</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simple modal functions
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showRoomModal(floorId) {
            document.getElementById('roomFloorId').value = floorId;
            showModal('roomModal');
        }

        function editFloorName(floorId, currentName) {
            document.getElementById('editFloorId').value = floorId;
            document.getElementById('editFloorName').value = currentName;
            showModal('editFloorModal');
        }

        // Tab functions
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Activate clicked tab button
            event.target.classList.add('active');
        }

        function showFloorTab(tabId) {
            // Hide all floor tab contents
            document.querySelectorAll('#floorModal .tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all floor tab buttons
            document.querySelectorAll('#floorModal .tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Activate clicked tab button
            event.target.classList.add('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Navigation toggle
        document.querySelector('.toggle').onclick = function(){
            document.querySelector('.navigation').classList.toggle('active');
            document.querySelector('.main').classList.toggle('active');
        }
    </script>
</body>
</html>