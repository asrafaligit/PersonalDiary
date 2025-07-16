<?php
  $conn3 = new mysqli("localhost", "root", "", "personalDiary");
  // Check connection
  if ($conn3->connect_error) {
      die("Connection failed: " . $conn3->connect_error);
  }
  session_start();
  $uname = $_SESSION['uname'];
  $que = "SELECT * FROM `personaldiary`.`users` WHERE username = '$uname';";
  $res = $conn3->query($que);
  $row = $res->fetch_assoc();
  $name = $row['name'];
  $login_date = $row['login_date'];
  $que2 = "SELECT COUNT(*) AS count FROM `personaldiary`.`diary_entries` WHERE diary_username = '$uname';";
  $que3 = "SELECT * FROM diary_entries WHERE diary_username = '$uname' ORDER BY diary_date DESC LIMIT 1";
$res1 = $conn3->query($que3);
$flage = true;

if ($res1->num_rows > 0) {
  // Output the last saved diary entry
  $last_saved = $res1->fetch_assoc();
  $last_entry_date = $last_saved['diary_date'];
}else {
    $flage = false;
}

  ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
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

        .account-info-container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .user-info {
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
<header>
    <nav>
        <button id="homeBtn"><a href="homepage.php">Home</a></button>
        <button id="reminderbtn"><a href="reminderPage.php">Reminder</a></button>
        <button id="reminderbtn"><a href="displayReminder.php">Your Reminder</a></button>
        <button id="dateBtn"><a href="displayPage.php">Your Diaries</a></button>
        <button id="logoutBtn" name="logoutBtn">Logout</button>
    </nav>
</header>

<main>
    <div class="account-info-container">
        <h2>Account Information</h2>
        <div class="user-info">
            <p><strong>Name: </strong><?php echo $name; ?></p>
            <p><strong>Username: </strong><?php echo $uname; ?></p>
            <p><strong>Account Created: </strong><?php echo $login_date; ?></p>
            <?php
            // Fetch the count from the SQL result set
            $count_row = $conn3->query($que2)->fetch_assoc();
            $diary_count = $count_row['count']; // Assuming 'count' is the alias
            ?>
            <p><strong>Total Diaries Saved: </strong><?php echo $diary_count; ?></p>
            <p><strong>Last Saved Diary: </strong><?php if ($flage) echo $last_entry_date; else echo "No diary entries found for this user."; ?></p>
        </div>
    </div>
</main>


<script>
    // Add event listener to the logout button
    document.getElementById("logoutBtn").addEventListener("click", function() {
        // Send an AJAX request to destroy the session
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                // Redirect to the sign-in page after destroying the session
                window.location.href = "sign_signup.php";
            }
        };
        xhttp.open("GET", "sign_signup.php", true);
        xhttp.send();
    });
</script>
</body>
</html>
