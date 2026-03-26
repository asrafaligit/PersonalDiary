<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$message = get_flash();

$entriesStmt = db()->prepare(
    "SELECT id, title, content, diary_date
     FROM diary_entries
     WHERE diary_username = ?
     ORDER BY diary_date DESC, id DESC"
);
$entriesStmt->bind_param("s", $username);
$entriesStmt->execute();
$entriesResult = $entriesStmt->get_result();
$entries = [];

while ($row = $entriesResult->fetch_assoc()) {
    $entries[] = $row;
}

$entriesStmt->close();

$count = count($entries);
$lastSaved = $entries[0]["diary_date"] ?? "None";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Diaries</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("dashboard")); ?>">
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="homepage.php">
                <span class="brand-title">Personal Diary</span>
                <span class="brand-subtitle">Your saved pages, all in one place</span>
            </a>
            <nav class="nav-links" aria-label="Primary">
                <a class="nav-link" href="homepage.php">Home</a>
                <a class="nav-link" href="reminderPage.php">Reminder</a>
                <a class="nav-link" href="displayReminder.php">Your Reminder</a>
                <a class="nav-link is-active" href="displayPage.php">Your Diaries</a>
                <a class="nav-link" href="profile.php">Profile</a>
            </nav>
        </header>

        <main class="main-grid">
            <section class="hero">
                <article class="panel hero-panel">
                    <div class="panel-inner">
                        <p class="eyebrow">Archive</p>
                        <h1 class="hero-title">Read back through the days you decided to keep.</h1>
                        <p class="hero-copy">
                            Your entries are listed from newest to oldest so you can revisit recent days fast,
                            edit what needs polishing, or remove what no longer belongs.
                        </p>
                    </div>
                </article>

                <aside class="hero-aside">
                    <div class="panel mini-card">
                        <h3>Total saved</h3>
                        <p><?php echo h((string) $count); ?> diary entr<?php echo $count === 1 ? "y" : "ies"; ?></p>
                    </div>
                    <div class="panel mini-card">
                        <h3>Latest page</h3>
                        <p><?php echo h($lastSaved); ?></p>
                        <p class="helper-text">Entries are sorted by saved date, newest first.</p>
                    </div>
                </aside>
            </section>

            <section class="panel section-card">
                <div class="section-heading">
                    <div>
                        <h2 class="section-title">Saved Diaries</h2>
                        <p class="section-copy">Edit details, keep what matters, and prune the rest.</p>
                    </div>
                    <a class="btn btn-primary" href="homepage.php">Write a new entry</a>
                </div>

                <?php if ($message): ?>
                    <div class="notice notice-<?php echo h($message["type"]); ?>">
                        <?php echo h($message["message"]); ?>
                    </div>
                <?php endif; ?>

                <?php if ($entries): ?>
                    <div class="entry-list">
                        <?php foreach ($entries as $entry): ?>
                            <article class="entry-card">
                                <h3><?php echo h($entry["title"] !== "" ? $entry["title"] : "Untitled entry"); ?></h3>
                                <div class="entry-meta">
                                    <span class="chip"><?php echo h($entry["diary_date"]); ?></span>
                                </div>
                                <p><?php echo nl2br(h($entry["content"])); ?></p>
                                <form
                                    class="entry-actions"
                                    action="delete_diary.php"
                                    method="post"
                                    onsubmit="return confirm('Delete this diary entry?');"
                                >
                                    <input type="hidden" name="id" value="<?php echo h((string) $entry["id"]); ?>">
                                    <a class="btn btn-secondary" href="edit_diary.php?id=<?php echo h((string) $entry["id"]); ?>">Edit</a>
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No diary entries yet</h3>
                        <p>Your journal is ready. Write your first page and it will appear here.</p>
                        <p><a class="link-inline" href="homepage.php">Create your first entry</a></p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
