<?php
require_once __DIR__ . "/bootstrap.php";

if (!empty($_SESSION["uname"])) {
    redirect("homepage.php");
}

$message = get_flash();
$activeTab = "signin";
$signinUsername = "";
$signupName = "";
$signupUsername = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mode = $_POST["mode"] ?? "signin";
    $activeTab = $mode === "signup" ? "signup" : "signin";

    if ($activeTab === "signin") {
        $signinUsername = trim($_POST["txtnameu"] ?? "");
        $password = $_POST["txtpassun"] ?? "";

        if ($signinUsername === "" || $password === "") {
            $message = [
                "type" => "error",
                "message" => "Please enter your username and password.",
            ];
        } else {
            $user = find_user_by_username($signinUsername);

            if (!$user || !password_matches($password, $user["password"])) {
                $message = [
                    "type" => "error",
                    "message" => "Invalid username or password.",
                ];
            } else {
                upgrade_password_if_needed($user["username"], $password, $user["password"]);
                $_SESSION["uname"] = $user["username"];
                set_flash("success", "Welcome back, {$user["name"]}.");
                redirect("homepage.php");
            }
        }
    } else {
        $signupName = trim($_POST["name"] ?? "");
        $signupUsername = trim($_POST["newusername"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["newpassword"] ?? "";

        if ($signupName === "" || $signupUsername === "" || $password === "" || $confirmPassword === "") {
            $message = [
                "type" => "error",
                "message" => "Please complete every sign-up field.",
            ];
        } elseif (!preg_match("/^[A-Za-z0-9_]{3,30}$/", $signupUsername)) {
            $message = [
                "type" => "error",
                "message" => "Username must be 3 to 30 characters and use only letters, numbers, or underscores.",
            ];
        } elseif (mb_strlen($signupName) < 2) {
            $message = [
                "type" => "error",
                "message" => "Please enter your full name.",
            ];
        } elseif (strlen($password) < 6) {
            $message = [
                "type" => "error",
                "message" => "Password should be at least 6 characters long.",
            ];
        } elseif ($password !== $confirmPassword) {
            $message = [
                "type" => "error",
                "message" => "Passwords do not match.",
            ];
        } elseif (find_user_by_username($signupUsername)) {
            $message = [
                "type" => "error",
                "message" => "That username already exists. Please choose another one.",
            ];
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $createdOn = date("Y-m-d");
            $stmt = db()->prepare(
                "INSERT INTO users (name, username, password, login_date) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $signupName, $signupUsername, $hashedPassword, $createdOn);
            $stmt->execute();
            $stmt->close();

            $_SESSION["uname"] = $signupUsername;
            set_flash("success", "Account created successfully.");
            redirect("homepage.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Personal Diary</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("auth")); ?>">
    <div class="auth-shell">
        <main class="auth-layout">
            <section class="auth-hero">
                <p class="eyebrow">Personal Diary</p>
                <h1 class="hero-title">A warm, private corner for your thoughts.</h1>
                <p>
                    Capture the shape of your days, keep your reminders close, and come back to the moments
                    that mattered without digging through clutter.
                </p>
                <div class="muted-box">
                    Sign in to continue where you left off, or create a fresh account and start journaling today.
                </div>
            </section>

            <section class="auth-card">
                <div class="auth-card-inner">
                    <div class="auth-tabs" role="tablist" aria-label="Authentication tabs">
                        <button
                            class="auth-tab<?php echo $activeTab === "signin" ? " is-active" : ""; ?>"
                            type="button"
                            data-auth-tab="signin"
                        >
                            Sign In
                        </button>
                        <button
                            class="auth-tab<?php echo $activeTab === "signup" ? " is-active" : ""; ?>"
                            type="button"
                            data-auth-tab="signup"
                        >
                            Sign Up
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="notice notice-<?php echo h($message["type"]); ?>">
                            <?php echo h($message["message"]); ?>
                        </div>
                    <?php endif; ?>

                    <form
                        class="auth-form<?php echo $activeTab === "signin" ? " is-active" : ""; ?>"
                        action="sign_signup.php"
                        method="post"
                        data-auth-form="signin"
                    >
                        <input type="hidden" name="mode" value="signin">
                        <div>
                            <h2 class="auth-title">Welcome back</h2>
                            <p class="auth-copy">Open your journal, review your reminders, and pick up where you paused.</p>
                        </div>

                        <div class="field">
                            <label for="txtnameu">Username</label>
                            <input
                                type="text"
                                id="txtnameu"
                                name="txtnameu"
                                value="<?php echo h($signinUsername); ?>"
                                placeholder="Enter your username"
                                required
                            >
                        </div>

                        <div class="field">
                            <label for="txtpassun">Password</label>
                            <input
                                type="password"
                                id="txtpassun"
                                name="txtpassun"
                                placeholder="Enter your password"
                                required
                            >
                        </div>

                        <div class="auth-actions">
                            <button class="btn btn-primary" type="submit">Sign in</button>
                            <button class="btn btn-ghost" type="button" data-auth-tab="signup">Create account</button>
                        </div>
                    </form>

                    <form
                        class="auth-form<?php echo $activeTab === "signup" ? " is-active" : ""; ?>"
                        action="sign_signup.php"
                        method="post"
                        data-auth-form="signup"
                    >
                        <input type="hidden" name="mode" value="signup">
                        <div>
                            <h2 class="auth-title">Create your account</h2>
                            <p class="auth-copy">Start with a simple account and keep your diary entries tied to you.</p>
                        </div>

                        <div class="field">
                            <label for="name">Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?php echo h($signupName); ?>"
                                placeholder="Your name"
                                required
                            >
                        </div>

                        <div class="field">
                            <label for="newusername">Username</label>
                            <input
                                type="text"
                                id="newusername"
                                name="newusername"
                                value="<?php echo h($signupUsername); ?>"
                                placeholder="Choose a username"
                                required
                            >
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="At least 6 characters"
                                required
                            >
                        </div>

                        <div class="field">
                            <label for="newpassword">Confirm password</label>
                            <input
                                type="password"
                                id="newpassword"
                                name="newpassword"
                                placeholder="Repeat your password"
                                required
                            >
                        </div>

                        <div class="auth-actions">
                            <button class="btn btn-primary" type="submit">Create account</button>
                            <button class="btn btn-ghost" type="button" data-auth-tab="signin">Back to sign in</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        const authTabs = document.querySelectorAll("[data-auth-tab]");
        const authForms = document.querySelectorAll("[data-auth-form]");

        function activateTab(tabName) {
            authTabs.forEach((tab) => {
                tab.classList.toggle("is-active", tab.dataset.authTab === tabName);
            });

            authForms.forEach((form) => {
                form.classList.toggle("is-active", form.dataset.authForm === tabName);
            });
        }

        authTabs.forEach((tab) => {
            tab.addEventListener("click", () => activateTab(tab.dataset.authTab));
        });
    </script>
</body>
</html>
