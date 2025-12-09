<?php
session_start();
// Temporarily disable login for testing
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php");
//     exit();
// }

require_once 'db.php';

echo "<h1>Simple Test</h1>";
echo "<p>If you can see this, PHP is working!</p>";

// Test database
if ($conn) {
    echo "<p style='color: green;'>Database connected successfully</p>";
    
    // Create tables if they don't exist
    $conn->query("CREATE TABLE IF NOT EXISTS buildings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        color VARCHAR(20) DEFAULT '#3498db',
        icon VARCHAR(50) DEFAULT 'fas fa-building',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "<p style='color: green;'>Tables created/checked</p>";
} else {
    echo "<p style='color: red;'>Database connection failed</p>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Form Submitted!</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_POST['name'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $color = $conn->real_escape_string($_POST['color'] ?? '#3498db');
        $icon = $conn->real_escape_string($_POST['icon'] ?? 'fas fa-building');
        
        $sql = "INSERT INTO buildings (name, description, color, icon) VALUES ('$name', '$description', '$color', '$icon')";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>Building added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    }
}

// Show existing buildings
$buildings = $conn->query("SELECT * FROM buildings")->fetch_all(MYSQLI_ASSOC);
echo "<h3>Existing Buildings:</h3>";
foreach ($buildings as $building) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:5px;'>";
    echo "<strong>" . htmlspecialchars($building['name']) . "</strong><br>";
    echo htmlspecialchars($building['description']);
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; padding: 20px; margin: 100px auto; width: 300px; border-radius: 10px; }
    </style>
</head>
<body>
    <button onclick="document.getElementById('testModal').style.display='block'">Test Add Building</button>

    <div class="modal" id="testModal">
        <div class="modal-content">
            <h3>Add Building</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Building Name" required><br><br>
                <textarea name="description" placeholder="Description"></textarea><br><br>
                <button type="submit">Add Building</button>
                <button type="button" onclick="document.getElementById('testModal').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('testModal')) {
                document.getElementById('testModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>