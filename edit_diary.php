<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$id = request_id();

if ($id === null) {
    set_flash("error", "Choose a diary entry to edit first.");
    redirect("displayPage.php");
}

$message = get_flash();
$title = "";
$content = "";
$diaryDate = "";

$entryStmt = db()->prepare(
    "SELECT id, title, content, diary_date
     FROM diary_entries
     WHERE id = ? AND diary_username = ?
     LIMIT 1"
);
$entryStmt->bind_param("is", $id, $username);
$entryStmt->execute();
$entry = $entryStmt->get_result()->fetch_assoc();
$entryStmt->close();

if (!$entry) {
    set_flash("error", "That diary entry could not be found.");
    redirect("displayPage.php");
}

$title = $entry["title"];
$content = $entry["content"];
$diaryDate = $entry["diary_date"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if ($content === "") {
        $message = [
            "type" => "error",
            "message" => "The diary content cannot be empty.",
        ];
    } else {
        $updateStmt = db()->prepare(
            "UPDATE diary_entries
             SET title = ?, content = ?
             WHERE id = ? AND diary_username = ?"
        );
        $updateStmt->bind_param("ssis", $title, $content, $id, $username);
        $updateStmt->execute();
        $updateStmt->close();

        set_flash("success", "Diary entry updated successfully.");
        redirect("displayPage.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Diary Entry</title>
    <link rel="stylesheet" href="app.css">
</head>
<body class="<?php echo h(body_class("dashboard")); ?>">
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="homepage.php">
                <span class="brand-title">Personal Diary</span>
                <span class="brand-subtitle">Refine a page without losing its original date</span>
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
            <section class="panel form-card">
                <div class="section-heading">
                    <div>
                        <h1 class="section-title">Edit Diary Entry</h1>
                        <p class="section-copy">Saved on <?php echo h($diaryDate); ?>. Update the title or content below.</p>
                    </div>
                    <span class="chip accent">Editing mode</span>
                </div>

                <?php if ($message): ?>
                    <div class="notice notice-<?php echo h($message["type"]); ?>">
                        <?php echo h($message["message"]); ?>
                    </div>
                <?php endif; ?>

                <form class="form-stack" action="edit_diary.php" method="post">
                    <input type="hidden" name="id" value="<?php echo h((string) $id); ?>">

                    <div class="field">
                        <label for="title">Title (optional)</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            maxlength="255"
                            value="<?php echo h($title); ?>"
                            placeholder="Untitled entry"
                        >
                    </div>

                    <div class="field">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" required><?php echo h($content); ?></textarea>
                    </div>

                    <div class="btn-row">
                        <button class="btn btn-primary" type="submit">Save changes</button>
                        <a class="btn btn-ghost" href="displayPage.php">Back to diaries</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
