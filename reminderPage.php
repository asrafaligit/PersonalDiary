<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$user = current_user();
$message = get_flash();
$reminderDate = date("Y-m-d");
$reminderMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reminderDate = trim($_POST["reminderDate"] ?? "");
    $reminderMessage = trim($_POST["reminderMessage"] ?? "");

    if ($reminderDate === "" || $reminderMessage === "") {
        $message = [
            "type" => "error",
            "message" => "Please choose a date and write the reminder message.",
        ];
    } elseif ($reminderDate < date("Y-m-d")) {
        $message = [
            "type" => "error",
            "message" => "Reminders should be set for today or a future date.",
        ];
    } else {
        $stmt = db()->prepare(
            "INSERT INTO reminder (reminder_date, message, reminder_username) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $reminderDate, $reminderMessage, $username);
        $stmt->execute();
        $stmt->close();

        set_flash("success", "Reminder saved successfully.");
        redirect("reminderPage.php");
    }
}

$statsStmt = db()->prepare(
    "SELECT COUNT(*) AS total_reminders,
            MIN(CASE WHEN reminder_date >= CURDATE() THEN reminder_date END) AS next_reminder
     FROM reminder
     WHERE reminder_username = ?"
);
$statsStmt->bind_param("s", $username);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

$upcomingStmt = db()->prepare(
    "SELECT reminder_date, message
     FROM reminder
     WHERE reminder_username = ?
     ORDER BY reminder_date ASC, id ASC
     LIMIT 3"
);
$upcomingStmt->bind_param("s", $username);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();
$upcomingReminders = [];

while ($row = $upcomingResult->fetch_assoc()) {
    $upcomingReminders[] = $row;
}

$upcomingStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("dashboard")); ?>">
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="homepage.php">
                <span class="brand-title">Personal Diary</span>
                <span class="brand-subtitle">Make sure future-you gets the note</span>
            </a>
            <nav class="nav-links" aria-label="Primary">
                <a class="nav-link" href="homepage.php">Home</a>
                <a class="nav-link is-active" href="reminderPage.php">Reminder</a>
                <a class="nav-link" href="displayReminder.php">Your Reminder</a>
                <a class="nav-link" href="displayPage.php">Your Diaries</a>
                <a class="nav-link" href="profile.php">Profile</a>
            </nav>
        </header>

        <main class="main-grid">
            <section class="hero">
                <article class="panel hero-panel">
                    <div class="panel-inner">
                        <p class="eyebrow">Reminders</p>
                        <h1 class="hero-title"><?php echo h($user["name"]); ?>, turn intentions into something you will actually see again.</h1>
                        <p class="hero-copy">
                            Save the date, leave yourself context, and keep your journal connected to the things you
                            do not want slipping through the cracks.
                        </p>
                    </div>
                </article>

                <aside class="hero-aside">
                    <div class="panel mini-card">
                        <h3>Quick view</h3>
                        <p><?php echo h(date("l, d F Y")); ?></p>
                        <p class="helper-text">Your reminders stay tied to your account.</p>
                    </div>
                    <div class="panel mini-card">
                        <h3>Reminder snapshot</h3>
                        <ul>
                            <li>Total reminders: <?php echo h((string) ($stats["total_reminders"] ?? 0)); ?></li>
                            <li>Next due date: <?php echo h($stats["next_reminder"] ?: "No upcoming reminder"); ?></li>
                            <li>Suggested habit: set reminders with context, not just dates.</li>
                        </ul>
                    </div>
                </aside>
            </section>

            <section class="content-grid">
                <article class="panel form-card">
                    <div class="section-heading">
                        <div>
                            <h2 class="section-title">Set Reminder</h2>
                            <p class="section-copy">Choose the date you want to be nudged, then write a note your future self will understand.</p>
                        </div>
                        <span class="chip">Keep it practical</span>
                    </div>

                    <?php if ($message): ?>
                        <div class="notice notice-<?php echo h($message["type"]); ?>">
                            <?php echo h($message["message"]); ?>
                        </div>
                    <?php endif; ?>

                    <form class="form-stack" action="reminderPage.php" method="post">
                        <div class="field">
                            <label for="reminderDate">Reminder date</label>
                            <input
                                type="date"
                                id="reminderDate"
                                name="reminderDate"
                                min="<?php echo h(date("Y-m-d")); ?>"
                                value="<?php echo h($reminderDate); ?>"
                                required
                            >
                        </div>

                        <div class="field">
                            <label for="reminderMessage">Message</label>
                            <textarea
                                id="reminderMessage"
                                name="reminderMessage"
                                placeholder="What should you remember, and why will it matter on that date?"
                                required
                            ><?php echo h($reminderMessage); ?></textarea>
                        </div>

                        <div class="btn-row">
                            <button class="btn btn-primary" type="submit">Save reminder</button>
                            <a class="btn btn-ghost" href="displayReminder.php">View all reminders</a>
                        </div>
                    </form>
                </article>

                <aside class="panel section-card">
                    <div class="section-heading">
                        <div>
                            <h2 class="section-title">Coming Up</h2>
                            <p class="section-copy">A quick glance at your nearest reminders.</p>
                        </div>
                    </div>

                    <?php if ($upcomingReminders): ?>
                        <div class="reminder-list">
                            <?php foreach ($upcomingReminders as $item): ?>
                                <div class="reminder-card">
                                    <h3><?php echo h($item["reminder_date"]); ?></h3>
                                    <p><?php echo nl2br(h($item["message"])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>No reminders yet</h3>
                            <p>Start with one thoughtful note for a future date and build from there.</p>
                        </div>
                    <?php endif; ?>
                </aside>
            </section>
        </main>
    </div>
</body>
</html>
