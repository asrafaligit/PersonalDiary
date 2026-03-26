<?php
$host = "localhost";
$user = "root";
$pass = "";
$database = "personaldiary";

$isCli = PHP_SAPI === "cli";
$shouldReset = $isCli
    ? in_array("--reset", $argv, true)
    : isset($_GET["reset"]) && $_GET["reset"] === "1";

$mysqli = new mysqli($host, $user, $pass);

if ($mysqli->connect_error) {
    http_response_code(500);
    exit("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

$statements = [
    "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
    "USE `{$database}`",
];

if ($shouldReset) {
    $statements[] = "SET FOREIGN_KEY_CHECKS = 0";
    $statements[] = "DROP TABLE IF EXISTS `reminder`";
    $statements[] = "DROP TABLE IF EXISTS `diary_entries`";
    $statements[] = "DROP TABLE IF EXISTS `users`";
    $statements[] = "SET FOREIGN_KEY_CHECKS = 1";
}

$statements[] = <<<SQL
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `login_date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

$statements[] = <<<SQL
CREATE TABLE IF NOT EXISTS `diary_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `diary_date` DATE NOT NULL,
  `diary_username` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_diary_entries_username` (`diary_username`),
  CONSTRAINT `fk_diary_entries_user`
    FOREIGN KEY (`diary_username`) REFERENCES `users` (`username`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

$statements[] = <<<SQL
CREATE TABLE IF NOT EXISTS `reminder` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reminder_date` DATE NOT NULL,
  `message` TEXT NOT NULL,
  `reminder_username` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reminder_username` (`reminder_username`),
  CONSTRAINT `fk_reminder_user`
    FOREIGN KEY (`reminder_username`) REFERENCES `users` (`username`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

$results = [];

foreach ($statements as $statement) {
    if ($mysqli->query($statement) === true) {
        $results[] = "[OK] " . preg_replace("/\s+/", " ", trim($statement));
        continue;
    }

    http_response_code(500);
    $results[] = "[ERROR] " . $mysqli->error;
    break;
}

$mysqli->close();

if ($isCli) {
    echo implode(PHP_EOL, $results) . PHP_EOL;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recreate Tables</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background: #f7f7f7;
            color: #222;
        }

        .card {
            max-width: 900px;
            margin: 0 auto;
            padding: 1.5rem;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        code,
        pre {
            background: #f1f1f1;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
        }

        pre {
            white-space: pre-wrap;
            padding: 1rem;
        }

        .warning {
            color: #9a3412;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Database Table Setup</h1>
        <p>This script creates the tables used by the app: <code>users</code>, <code>diary_entries</code>, and <code>reminder</code>.</p>
        <p class="warning">Add <code>?reset=1</code> to the URL only when you want to drop the current tables first and rebuild them from scratch.</p>
        <pre><?php echo htmlspecialchars(implode(PHP_EOL, $results), ENT_QUOTES, "UTF-8"); ?></pre>
    </div>
</body>
</html>
