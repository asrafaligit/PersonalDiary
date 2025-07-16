<?php
          if (isset($_POST['submit'])) {
              $conn2 = new mysqli("localhost", "root", "", "personaldiary");
              // Check connection
              if ($conn2->connect_error) {
                die("Connection failed: " . $conn2->connect_error);
              }
              $name = $_POST['title'];
              $content = $_POST['content'];
              $diary_date = date('Y-m-d'); // Get current date and time
              $sql2 = "INSERT INTO `diary_entries`(`title`, `content`, `diary_date`,`diary_username`) VALUES ('$name','$content','$diary_date','$uname')";
              if ($conn2->query($sql2) === TRUE) {
                echo '<script>alert("Diary saved Successful!")</script>';
              } else {
                echo '<p class="db" style="border:solid; border-color:red; padding:8pt;">Sorry Fatal error in saving your diary!!!</p>';
              }
            $conn2->close();
          }
        ?>