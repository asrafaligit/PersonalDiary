<?php
// Establish database connection
$conn = new mysqli("localhost", "root", "", "personaldiary");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Fetch diary entry to edit
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM diary_entries WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $content = $row['content'];
    } else {
        echo "Diary entry not found";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Diary Entry</title>
    <style>
        body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
      }

      .entry-section {
        max-width: 600px;
        margin: 0 auto;
      }

        form input,
      form textarea {
        width: 100%;
        padding: 0.5rem;
        margin-bottom: 1rem;
      }

      form button {
        background-color: #626060;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        cursor: pointer;
      }

      form button:hover {
        background-color: #555;
      }
    </style>
</head>
<body>
<section class="entry-section">
    <h1>Edit Diary Entry</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <textarea name="content"><?php echo $content; ?></textarea>
        <button type="submit">Update</button>
    </form>
</section>
    <?php
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get ID and updated content from form
    $id = $_POST['id'];
    $updatedContent = $_POST['content'];

    // Update diary entry
    $sql = "UPDATE diary_entries SET content='$updatedContent' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo '<script>
    if(confirm("Diary saved Successful! Do you want to proceed to the display page?")) {
        window.location.href = "displayPage.php";
    }
</script>'; 
        exit();
    } else {
        echo "Error updating diary entry: " . $conn->error;
    }
}

// Close connection
$conn->close();
?>
</body>
</html>
