<?php
// Establish database connection
$conn = new mysqli("localhost", "root", "", "personaldiary");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
        if(isset($_SESSION['uname'])) {
          $uname = $_SESSION['uname'];
// Fetch diary entries
$sql = "SELECT * FROM diary_entries WHERE diary_username='$uname'";
$result = $conn->query($sql);

// Initialize an array to store entries
$entries = [];

// Check if there are results
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
      $entry = [
        'id' => $row['id'],
        'title' => $row['title'],
        'content' => $row['content'],
        'date' => $row['diary_date'] // Assuming 'date' is the column name for the date
      ];
        $entries[] = $entry;
    }
}else {
  echo "You have not written any diary yet please write one";
}
}else{
  echo "You are not logged in";
}

// Close connection
$conn->close();

// Return entries as JSON
echo json_encode($entries);
?>
