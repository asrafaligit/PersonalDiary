<?php
// Establish database connection
$conn = new mysqli("localhost", "root", "", "personaldiary");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get ID of diary entry to delete
$id = $_GET['id'];

// Delete diary entry
$sql = "DELETE FROM reminder WHERE id=$id";
if ($conn->query($sql) === TRUE) {
    echo "Diary entry deleted successfully";
} else {
    echo "Error deleting diary entry: " . $conn->error;
}

// Close connection
$conn->close();
?>
