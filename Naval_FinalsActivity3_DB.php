<?php
// database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "loan_management_jn";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check if connected
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>