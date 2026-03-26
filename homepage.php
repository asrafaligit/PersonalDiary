<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$user = current_user();
$message = get_flash();
$title = "";
$content = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if ($content === "") {
        $message = [
            "type" => "error",
            "message" => "Please write something in your diary entry before saving.",
        ];
    } else {
        $diaryDate = date("Y-m-d");
        $stmt = db()->prepare(
            "INSERT INTO diary_entries (title, content, diary_date, diary_username) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $title, $content, $diaryDate, $username);
        $stmt->execute();
        $stmt->close();

        set_flash("success", "Diary entry saved successfully.");
        redirect("homepage.php");
    }
}

$statsStmt = db()->prepare(
    "SELECT COUNT(*) AS total_entries, MAX(diary_date) AS last_entry_date FROM diary_entries WHERE diary_username = ?"
);
$statsStmt->bind_param("s", $username);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

$reminderStmt = db()->prepare(
    "SELECT COUNT(*) AS total_upcoming, MIN(reminder_date) AS next_reminder
     FROM reminder
     WHERE reminder_username = ? AND reminder_date >= CURDATE()"
);
$reminderStmt->bind_param("s", $username);
$reminderStmt->execute();
$reminderStats = $reminderStmt->get_result()->fetch_assoc();
$reminderStmt->close();

$latestEntryStmt = db()->prepare(
    "SELECT title, diary_date
     FROM diary_entries
     WHERE diary_username = ?
     ORDER BY diary_date DESC, id DESC
     LIMIT 1"
);
$latestEntryStmt->bind_param("s", $username);
$latestEntryStmt->execute();
$latestEntry = $latestEntryStmt->get_result()->fetch_assoc();
$latestEntryStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Diary</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("dashboard")); ?>">
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="homepage.php">
                <span class="brand-title">Personal Diary</span>
                <span class="brand-subtitle">A calmer place to capture your day</span>
            </a>
            <nav class="nav-links" aria-label="Primary">
                <a class="nav-link is-active" href="homepage.php">Home</a>
                <a class="nav-link" href="reminderPage.php">Reminder</a>
                <a class="nav-link" href="displayReminder.php">Your Reminder</a>
                <a class="nav-link" href="displayPage.php">Your Diaries</a>
                <a class="nav-link" href="profile.php">Profile</a>
            </nav>
        </header>

        <main class="main-grid">
            <section class="home-quickbar">
                <article class="panel quickbar-card">
                    <p class="stat-label">Today</p>
                    <p class="quickbar-value"><?php echo h(date("d M Y")); ?></p>
                </article>
                <article class="panel quickbar-card">
                    <p class="stat-label">Entries Saved</p>
                    <p class="quickbar-value"><?php echo h((string) ($stats["total_entries"] ?? 0)); ?></p>
                </article>
                <article class="panel quickbar-card">
                    <p class="stat-label">Upcoming Reminders</p>
                    <p class="quickbar-value"><?php echo h((string) ($reminderStats["total_upcoming"] ?? 0)); ?></p>
                </article>
                <article class="panel quickbar-card">
                    <p class="stat-label">Latest Entry</p>
                    <p class="quickbar-value"><?php echo h(($latestEntry["title"] ?? "") !== "" ? $latestEntry["title"] : "Untitled"); ?></p>
                </article>
            </section>

            <section class="panel diary-workspace">
                <div class="diary-workspace-head">
                    <div>
                        <h1 class="section-title">New Entry</h1>
                        <p class="section-copy">A full page, lined like paper. Just write.</p>
                    </div>
                    <span class="chip accent">Title optional</span>
                </div>

                <?php if ($message): ?>
                    <div class="notice notice-<?php echo h($message["type"]); ?>">
                        <?php echo h($message["message"]); ?>
                    </div>
                <?php endif; ?>

                <form action="homepage.php" method="post">
                    <div class="diary-sheet">
                        <div class="field diary-title-field">
                            <label for="titleInput">Title (optional)</label>
                            <input
                                class="diary-title-input"
                                type="text"
                                id="titleInput"
                                name="title"
                                maxlength="255"
                                placeholder="Untitled entry"
                                value="<?php echo h($title); ?>"
                            >
                        </div>

                        <div class="field diary-content-field">
                            <label for="contentInput" class="sr-only">Your entry</label>
                            <textarea
                                class="diary-entry-textarea"
                                id="contentInput"
                                name="content"
                                placeholder="Start writing here..."
                                required
                            ><?php echo h($content); ?></textarea>
                        </div>
                    </div>

                    <div class="btn-row diary-actions">
                        <button class="btn btn-primary" type="submit">Save diary entry</button>
                        <a class="btn btn-ghost" href="displayPage.php">Browse saved entries</a>
                    </div>
                </form>
            </section>

            <section class="stats-grid">
                <article class="panel stat-card">
                    <p class="stat-label">Entries Saved</p>
                    <p class="stat-value"><?php echo h((string) ($stats["total_entries"] ?? 0)); ?></p>
                </article>
                <article class="panel stat-card">
                    <p class="stat-label">Upcoming Reminders</p>
                    <p class="stat-value"><?php echo h((string) ($reminderStats["total_upcoming"] ?? 0)); ?></p>
                </article>
                <article class="panel stat-card">
                    <p class="stat-label">Last Entry Date</p>
                    <p class="stat-value"><?php echo h($stats["last_entry_date"] ?: "None"); ?></p>
                </article>
            </section>

            <section class="content-grid home-prompts-grid">
                <aside class="panel section-card">
                    <div class="section-heading">
                        <div>
                            <h2 class="section-title">Writing Prompts</h2>
                            <p class="section-copy">Use one if the page feels blank.</p>
                        </div>
                    </div>
                    <div class="entry-list">
                        <div class="entry-card">
                            <h3>What stayed with you?</h3>
                            <p>Write about one moment from today that keeps echoing in your mind and why it matters.</p>
                        </div>
                        <div class="entry-card">
                            <h3>What shifted?</h3>
                            <p>Capture one thing you understand differently now than you did this morning.</p>
                        </div>
                        <div class="entry-card">
                            <h3>What deserves gratitude?</h3>
                            <p>List something quiet and ordinary that made the day feel softer or stronger.</p>
                        </div>
                    </div>
                </aside>
            </section>
        </main>
    </div>
</body>
</html>
