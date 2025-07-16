<?php extract($_POST);
?>

<!DOCTYPE html>
<html>

<head>
    <title>signin/signup form</title>
   
    <style>
        * {
    padding-left: 0;
    margin: 0;
    font-family: sans-serif;
}

body {
    background: url(./img/diary1.jpg) no-repeat;
    background-size: cover;
    width: 100vh;
    height: 100vh;
}

.login-form {
    width: 350px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    position: absolute;
    color: rgb(240, 239, 239);
    background-color: rgb(208, 240, 179);
    padding: 35px;
    border-radius: 20px;
}

.login-form h1 {
    color: rgb(46, 162, 228);
    text-transform: uppercase;
    text-align: center;
    padding: auto;
    border-radius: 10px;
    text-decoration: underline;
}

.login-form P {
    font-size: 20px;
    color: rgb(0, 0, 0);
    margin: 15px 0;
}

.login-form input {
    margin-top: 30px;
    width: 270px;
    padding: 15px 10px;
    border: none;
    outline: none;
    border-radius: 5px;
    border-bottom: 1px solid black;
}

.login-form button {
    background-color: rgb(208, 240, 179);
    margin-top: 30PX;
    height: 40px;
    width: 100px;
    border-radius: 5px;
}

.logimg {
    margin-top: 5px;
}

.login-form button:hover {
    background-color: rgb(142, 137, 116);
    color: black;
}
    </style>
</head>

<body>


    <div id="signin" class="login-form">
        <form action="sign_signup.php" method="post">
            <h1>Login Form </h1>
            <div style="display: flex;">
                <div><img style="padding-top: 15px;" class="logimg" src="./img/user_name icon.png" alt="no user img" height="60px" width="50px"></div>
                <div><input type="text " name="txtnameu" placeholder="User Name" required></div>
            </div>
            <div style="display: flex;">
                <div><img style="padding-top: 15px;" class="logimg" src="./img/password icon.png" alt="no user img" height="60px" width="50px"></div>
                <div><input type="password" name="txtpassun" placeholder="Password" required></div>
            </div>
            <button type="submit" name="submit1">Login</button><br><br>
        </form>
        <?php
        $found = false;

        if (isset($_POST['txtpassun'])) {
            $conn3 = new mysqli("localhost", "root", "", "personaldiary");
            // Check connection
            if ($conn3->connect_error) {
                die("Connection failed: " . $conn3->connect_error);
            }
            $uname = $_POST['txtnameu'];
            session_start();
            $_SESSION['uname'] = $uname;

            $sql3 = "SELECT * FROM users";
            $result3 = $conn3->query($sql3);

            while ($row = $result3->fetch_assoc()) {

                if ($row["username"] == $txtnameu &&  $row["password"] == $txtpassun) {
                    $found = true;
                    setcookie('user_name', $row['username']);
                    // setcookie('user_email', $row['email']);
                    header("Location:homepage.php");  //redirect
                }
            }
            if ($found == false)
                echo '<p class="db" style="border:solid;border-color:red;padding:4px;"> Invalid Username/Password!!! </p> ';
            $conn3->close();
        }

        ?>
        <span style="color: black;">New Here! <a style="color:blue ;" href="# " onclick="signup() ">Click here</a>
            to sign-up</span>

    </div>
    <div id="signup" class="login-form " style="display:none; ">
        <form action="sign_signup.php" method="post">
            <h1>Signup Form </h1>
            <div style="display: flex;">
                <div><img style="padding-top: 19px;" class="logimg" src="./img/name.png" alt="name icon" height="60px" width="50px"></div>
                <div><input type="text " name="name" placeholder="Name"></div>
            </div>
            <div style="display: flex;">
                <div><img style="padding-top: 15px;" class="logimg" src="./img/user_name icon.png" alt="user icon" height="60px" width="50px"></div>
                <div><input type="text " name="newusername" placeholder="User Name"></div>
            </div>
            <div style="display: flex;">
                <div><img style="padding-top: 15px;" class="logimg" src="./img/password icon.png" alt="pass icon" height="60px" width="50px"></div>
                <div><input type="password" name="password" placeholder="Password"></div>
            </div>
            <div style="display: flex;">
                <div><img style="padding-top: 15px;" class="logimg" src="./img/password icon.png" alt="pass icon" height="60px" width="50px"></div>
                <div><input type="password" name="newpassword" placeholder="Confirm Password"></div>
            </div>
            <button type="submit" name="submit2">Signup</button>
        </form>
        <?php
    // $register = false;
    if (isset($_POST['submit2'])) {
        $conn2 = new mysqli("localhost", "root", "", "personaldiary");
        // Check connection
        if ($conn2->connect_error) {
            die("Connection failed: " . $conn2->connect_error);
        }
        $name = $_POST['name'];
        $newusername = $_POST['newusername'];
        $newpassword = $_POST['newpassword'];
        $password = $_POST['password'];
        $datetime = date('Y-m-d'); // Get current date and time
        
        if ($newpassword == $password) {
            $que = "SELECT username FROM `personaldiary`.`users` WHERE username = '$newusername';";
            $res = $conn2->query($que);
            if(mysqli_num_rows($res)>0){
                echo "<script>alert('Username already exists');</script>";
            } else {
                $sql2 = "INSERT INTO users(`name`, `username`, `password`, `login_date`) VALUES ('$name', '$newusername', '$newpassword', '$datetime');";
                if ($conn2->query($sql2) === TRUE) {
                    $register = true;
                    echo '<script>alert("Registration Successful!")</script>';
                } else {
                    echo '<p class="db" style="border:solid; border-color:red; padding:8pt;">Error in Registration!!!</p>';
                }
            }
        } else {
            echo "<script>alert('Passwords do not match');</script>";
        }
        $conn2->close();
    }
?>

        <br><span style="color: black;">Already have an account! <a style="color: blue;" href="# " onclick="signin() "> Click here
            </a>to sign-in</span>
        </form>
    </div>
    <script>
        function signin() {
            var div = document.getElementById('signin');
            var div2 = document.getElementById('signup');
            div.style.display = "block ";
            div2.style.display = "none ";
        }

        function signup() {
            var div = document.getElementById('signin');
            var div2 = document.getElementById('signup');
            div.style.display = "none ";
            div2.style.display = "block ";
        }
    </script>
</body>

</html>