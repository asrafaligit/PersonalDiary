<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$message = get_flash();

$remindersStmt = db()->prepare(
    "SELECT id, reminder_date, message
     FROM reminder
     WHERE reminder_username = ?
     ORDER BY reminder_date ASC, id ASC"
);
$remindersStmt->bind_param("s", $username);
$remindersStmt->execute();
$remindersResult = $remindersStmt->get_result();
$reminders = [];

while ($row = $remindersResult->fetch_assoc()) {
    $reminders[] = $row;
}

$remindersStmt->close();

$totalReminders = count($reminders);
$nextDue = "None";

foreach ($reminders as $item) {
    if ($item["reminder_date"] >= date("Y-m-d")) {
        $nextDue = $item["reminder_date"];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Reminders</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("dashboard")); ?>">
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="homepage.php">
                <span class="brand-title">Personal Diary</span>
                <span class="brand-subtitle">A tidy view of every reminder you saved</span>
            </a>
            <nav class="nav-links" aria-label="Primary">
                <a class="nav-link" href="homepage.php">Home</a>
                <a class="nav-link" href="reminderPage.php">Reminder</a>
                <a class="nav-link is-active" href="displayReminder.php">Your Reminder</a>
                <a class="nav-link" href="displayPage.php">Your Diaries</a>
                <a class="nav-link" href="profile.php">Profile</a>
            </nav>
        </header>

        <main class="main-grid">
            <section class="hero">
                <article class="panel hero-panel">
                    <div class="panel-inner">
                        <p class="eyebrow">Reminder List</p>
                        <h1 class="hero-title">See what is coming up before it sneaks past you.</h1>
                        <p class="hero-copy">
                            Keep your reminders readable, remove the ones you no longer need,
                            and use the list as a lightweight planning board.
                        </p>
                    </div>
                </article>

                <aside class="hero-aside">
                    <div class="panel mini-card">
                        <h3>Total reminders</h3>
                        <p><?php echo h((string) $totalReminders); ?></p>
                    </div>
                    <div class="panel mini-card">
                        <h3>Next due date</h3>
                        <p><?php echo h($nextDue); ?></p>
                        <p class="helper-text">Old reminders stay here until you remove them.</p>
                    </div>
                </aside>
            </section>

            <section class="panel section-card">
                <div class="section-heading">
                    <div>
                        <h2 class="section-title">Your Reminders</h2>
                        <p class="section-copy">Organized by date so the soonest one is easy to spot.</p>
                    </div>
                    <a class="btn btn-primary" href="reminderPage.php">Create reminder</a>
                </div>

                <?php if ($message): ?>
                    <div class="notice notice-<?php echo h($message["type"]); ?>">
                        <?php echo h($message["message"]); ?>
                    </div>
                <?php endif; ?>

                <?php if ($reminders): ?>
                    <div class="reminder-list">
                        <?php foreach ($reminders as $reminder): ?>
                            <article class="reminder-card">
                                <h3><?php echo h($reminder["reminder_date"]); ?></h3>
                                <p><?php echo nl2br(h($reminder["message"])); ?></p>
                                <form
                                    class="entry-actions"
                                    action="delete_reminder.php"
                                    method="post"
                                    onsubmit="return confirm('Delete this reminder?');"
                                >
                                    <input type="hidden" name="id" value="<?php echo h((string) $reminder["id"]); ?>">
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No reminders found</h3>
                        <p>Set one from the reminder page and it will show up here.</p>
                        <p><a class="link-inline" href="reminderPage.php">Create your first reminder</a></p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
