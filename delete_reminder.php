<?php
require_once __DIR__ . "/bootstrap.php";

$username = require_login();
$method = strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET");

if (!in_array($method, ["POST", "DELETE"], true)) {
    if (wants_json()) {
        json_response(["message" => "Method not allowed."], 405);
    }

    http_response_code(405);
    exit("Method not allowed.");
}

$id = request_id();

if ($id === null) {
    if (wants_json()) {
        json_response(["message" => "A valid reminder id is required."], 422);
    }

    set_flash("error", "A valid reminder id is required.");
    redirect("displayReminder.php");
}

$stmt = db()->prepare("DELETE FROM reminder WHERE id = ? AND reminder_username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();

if (wants_json()) {
    if ($deleted) {
        json_response(["message" => "Reminder deleted successfully."]);
    }

    json_response(["message" => "Reminder not found."], 404);
}

set_flash($deleted ? "success" : "error", $deleted ? "Reminder deleted successfully." : "Reminder not found.");
redirect("displayReminder.php");
