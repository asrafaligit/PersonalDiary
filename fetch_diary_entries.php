<?php
require_once __DIR__ . "/bootstrap.php";

if (empty($_SESSION["uname"])) {
    json_response(["message" => "You are not logged in."], 401);
}

$username = (string) $_SESSION["uname"];
$stmt = db()->prepare(
    "SELECT id, title, content, diary_date
     FROM diary_entries
     WHERE diary_username = ?
     ORDER BY diary_date DESC, id DESC"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$entries = [];

while ($row = $result->fetch_assoc()) {
    $entries[] = [
        "id" => $row["id"],
        "title" => $row["title"],
        "content" => $row["content"],
        "date" => $row["diary_date"],
    ];
}

$stmt->close();

json_response($entries);
