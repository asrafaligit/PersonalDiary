<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$user = current_user();
$message = get_flash();

$diaryStatsStmt = db()->prepare(
    "SELECT COUNT(*) AS total_diaries, MAX(diary_date) AS last_entry_date
     FROM diary_entries
     WHERE diary_username = ?"
);
$diaryStatsStmt->bind_param("s", $username);
$diaryStatsStmt->execute();
$diaryStats = $diaryStatsStmt->get_result()->fetch_assoc();
$diaryStatsStmt->close();

$reminderStatsStmt = db()->prepare(
    "SELECT COUNT(*) AS total_reminders,
            MIN(CASE WHEN reminder_date >= CURDATE() THEN reminder_date END) AS next_reminder
     FROM reminder
     WHERE reminder_username = ?"
);
$reminderStatsStmt->bind_param("s", $username);
$reminderStatsStmt->execute();
$reminderStats = $reminderStatsStmt->get_result()->fetch_assoc();
$reminderStatsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("dashboard")); ?>">
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="homepage.php">
                <span class="brand-title">Personal Diary</span>
                <span class="brand-subtitle">A quick view of your account and writing rhythm</span>
            </a>
            <nav class="nav-links" aria-label="Primary">
                <a class="nav-link" href="homepage.php">Home</a>
                <a class="nav-link" href="reminderPage.php">Reminder</a>
                <a class="nav-link" href="displayReminder.php">Your Reminder</a>
                <a class="nav-link" href="displayPage.php">Your Diaries</a>
                <a class="nav-link is-active" href="profile.php">Profile</a>
            </nav>
        </header>

        <main class="main-grid">
            <section class="hero">
                <article class="panel hero-panel">
                    <div class="panel-inner">
                        <p class="eyebrow">Profile</p>
                        <h1 class="hero-title"><?php echo h($user["name"]); ?>, here is your diary snapshot.</h1>
                        <p class="hero-copy">
                            Track how many entries you have written, when you joined, and what your reminder queue looks like.
                        </p>
                    </div>
                </article>

                <aside class="hero-aside">
                    <div class="panel mini-card">
                        <h3>Account created</h3>
                        <p><?php echo h($user["login_date"]); ?></p>
                    </div>
                    <div class="panel mini-card">
                        <h3>Next reminder</h3>
                        <p><?php echo h($reminderStats["next_reminder"] ?: "No upcoming reminder"); ?></p>
                        <p class="helper-text">Stay close to what is coming next.</p>
                    </div>
                </aside>
            </section>

            <section class="stats-grid">
                <article class="panel stat-card">
                    <p class="stat-label">Total Diaries</p>
                    <p class="stat-value"><?php echo h((string) ($diaryStats["total_diaries"] ?? 0)); ?></p>
                </article>
                <article class="panel stat-card">
                    <p class="stat-label">Total Reminders</p>
                    <p class="stat-value"><?php echo h((string) ($reminderStats["total_reminders"] ?? 0)); ?></p>
                </article>
                <article class="panel stat-card">
                    <p class="stat-label">Last Diary Date</p>
                    <p class="stat-value"><?php echo h($diaryStats["last_entry_date"] ?: "None"); ?></p>
                </article>
            </section>

            <section class="profile-grid">
                <article class="panel profile-card">
                    <div class="section-heading">
                        <div>
                            <h2 class="section-title">Account Details</h2>
                            <p class="section-copy">Basic information tied to your journal.</p>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="notice notice-<?php echo h($message["type"]); ?>">
                            <?php echo h($message["message"]); ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-list">
                        <div class="profile-row">
                            <strong>Name</strong>
                            <span><?php echo h($user["name"]); ?></span>
                        </div>
                        <div class="profile-row">
                            <strong>Username</strong>
                            <span><?php echo h($user["username"]); ?></span>
                        </div>
                        <div class="profile-row">
                            <strong>Joined</strong>
                            <span><?php echo h($user["login_date"]); ?></span>
                        </div>
                    </div>
                </article>

                <article class="panel profile-card">
                    <div class="section-heading">
                        <div>
                            <h2 class="section-title">Account Actions</h2>
                            <p class="section-copy">Move through the app or sign out cleanly.</p>
                        </div>
                    </div>

                    <div class="entry-list">
                        <div class="entry-card">
                            <h3>Keep writing</h3>
                            <p>Open a fresh page, review older entries, or add reminders without leaving your flow.</p>
                            <div class="entry-actions">
                                <a class="btn btn-primary" href="homepage.php">New entry</a>
                                <a class="btn btn-secondary" href="displayPage.php">View diaries</a>
                            </div>
                        </div>
                        <div class="entry-card">
                            <h3>Sign out</h3>
                            <p>Use a proper logout so your session is cleared instead of just jumping pages.</p>
                            <div class="entry-actions">
                                <a class="btn btn-danger" href="logout.php">Logout</a>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
