<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Diaries</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
      }

      header {
        background-color: #333;
        color: white;
        padding: 1rem;
      }

      nav {
        margin-left: 30rem;
      }
      nav button {
        background-color: #555;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        cursor: pointer;
      }

      a {
        color: white;
        text-decoration: none;
      }
      main {
        padding: 2rem;
      }

      .diary-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
      }

      .diary {
        border: 1px solid #ccc;
        padding: 20px;
        margin-bottom: 20px;
      }

      .navigation-buttons {
        margin-top: 20px;
        text-align: center;
      }

      .navigation-buttons button {
        background-color: #555;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        cursor: pointer;
      }

      .navigation-buttons button:hover {
        background-color: #333;
      }
    </style>
  </head>
  <body>
    <header>
      <nav>
        <button id="homeBtn"><a href="homePage.php">Home</a></button>
        <button id="reminderbtn"><a href="reminderPage.php">Reminder</a></button>
        <button id="reminderbtn"><a href="#">Your Reminder</a></button>
        <button id="dateBtn"><a href="displayPage.php">Your Diaries</a></button>
        <button id="accountBtn"><a href="profile.php">Profile</a></button>
      </nav>
    </header>
    <main>
    <Form action="displayReminder.php" method="post">
      <div class="diary-container">
        <h2>Your Reminders</h2>
        <?php
        // Replace the database connection details with your own
        $conn = new mysqli("localhost", "root", "", "personaldiary");
        if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
        }
        // Fetch diaries for the logged-in user
        session_start();
        if(isset($_SESSION['uname'])) {
          $uname = $_SESSION['uname'];
          $sql = "SELECT * FROM reminder WHERE reminder_username='$uname'";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              echo "<div class='diary'>";
              echo "<h3>" . $row['reminder_date'] . "</h3>";
              echo "<p>" . $row['message'] . "</p>";
              //delete buttons
              echo "<button name='delete' onclick='deleteDiary(" . $row['id'] . ")'>Delete</button>";
              echo "</div>";
            }
          } else {
            echo "No reminder found.";
          }
        } else {
          // Handle the case where the username session variable is not set
          echo "You are not logged in";
      }
          $conn->close();
        ?>
      </div>
    </main>
    <script>
// Function to delete a diary entry
        function deleteDiary(id) {
            if (confirm('Are you sure you want to delete this diary entry?')) {
                fetch(`delete_reminder.php?id=${id}`, { method: 'DELETE' })
                    .then(response => {
                        if (response.ok) {
                            // Reload the page to reflect the changes
                            window.location.reload();
                        } else {
                            console.error('Failed to delete diary entry');
                        }
                    })
                    .catch(error => console.error('Error deleting diary entry:', error));
            }
        }
    </script>   
    </main>
  </body>
</html>
