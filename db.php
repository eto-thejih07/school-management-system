<?php

$server = "sql202.infinityfree.com";
$dbusername = "if0_40252359";
$dbpassword = "200715903237";
$dbname = "if0_40252359_dmv";

$conn = new mysqli($server, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    echo "Error: " . $conn->connect_error;
}
$conn->set_charset("utf8");

?>
