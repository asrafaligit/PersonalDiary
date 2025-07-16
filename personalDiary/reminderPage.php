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
  // $login_date = $row['login_date'];
  // $uname = $row['uname'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reminder</title>
    <style>
      /* styles.css */
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

      .reminder-section {
        max-width: 600px;
        margin: 0 auto;
      }

      h2 {
        margin-bottom: 1rem;
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
    <header>
      <nav>
        <button id="homeBtn"><a href="homepage.php">Home</a></button>
        <button id="reminderbtn"><a href="#">Reminder</a></button>
        <button id="reminderbtn"><a href="displayReminder.php">Your Reminder</a></button>
        <button id="dateBtn"><a href="displayPage.php">Your Diaries</a></button>
        <button id="accountBtn"><a href="profile.php">Profile</a></button>
      </nav>
    </header>
    <main>        
      </section>
          <section class="reminder-section">
      <h2><u>Set Reminder</u></h2>
      <section id="presentDaySection">
            <h2>Present Day</h2>
            <p id="currentDateTime"></p>
          </section>
      <form id="reminderForm" action="reminderPage.php" method="post">
        <input type="date" id="reminderDate" name="reminderDate" required />
        <textarea id="reminderMessage" name="reminderMessage" placeholder="Enter reminder message..." required></textarea>
        <button type="submit" name="submit">Set Reminder</button>
      </form>
      <?php
          if (isset($_POST['submit'])) {
              $conn2 = new mysqli("localhost", "root", "", "personaldiary");
              // Check connection
              if ($conn2->connect_error) {
                die("Connection failed: " . $conn2->connect_error);
              }
              $reminder_date = date('Y-m-d'); // Get current date and time
              $message = $_POST['reminderMessage'];
              $sql2 = "INSERT INTO `reminder`( `reminder_date`,`message`,`reminder_username`) VALUES ('$reminder_date','$message','$uname')";
              if ($conn2->query($sql2) === TRUE) {
                echo '<script>alert("Reminder saved Successful!")</script>';
              } else {
                echo '<p class="db" style="border:solid; border-color:red; padding:8pt;">Sorry Fatal error in saving your diary!!!</p>';
              }
            $conn2->close();
          }
        ?>
    </section>

    </main>
    <script>
      // script.js
      const currentDateTimeElement = document.getElementById("currentDateTime");
      const currentDate = new Date();
      const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "numeric",
        minute: "numeric",
        second: "numeric",
      };
      currentDateTimeElement.textContent = currentDate.toLocaleDateString(
        "en-US",
        options
      );
    </script>
  </body>
</html>
