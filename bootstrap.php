<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function db(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli("localhost", "root", "", "personaldiary");
    $connection->set_charset("utf8mb4");

    return $connection;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION["flash"] = [
        "type" => $type,
        "message" => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION["flash"])) {
        return null;
    }

    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);

    return $flash;
}

function require_login(): string
{
    if (empty($_SESSION["uname"])) {
        set_flash("error", "Please sign in to continue.");
        redirect("sign_signup.php");
    }

    return (string) $_SESSION["uname"];
}

function current_user(): array
{
    static $user = null;

    if ($user !== null) {
        return $user;
    }

    $username = require_login();
    $stmt = db()->prepare("SELECT id, name, username, login_date FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION = [];
        session_destroy();
        session_start();
        set_flash("error", "Your account session expired. Please sign in again.");
        redirect("sign_signup.php");
    }

    return $user;
}

function request_id(string $key = "id"): ?int
{
    $value = $_POST[$key] ?? $_GET[$key] ?? null;

    if ($value === null || $value === "") {
        return null;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT);

    return $id === false ? null : $id;
}

function wants_json(): bool
{
    $accept = $_SERVER["HTTP_ACCEPT"] ?? "";
    $requestedWith = strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "");
    $method = strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET");

    return str_contains($accept, "application/json")
        || $requestedWith === "xmlhttprequest"
        || $method === "DELETE";
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function password_matches(string $plainPassword, string $storedPassword): bool
{
    $info = password_get_info($storedPassword);

    if (!empty($info["algo"])) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, $plainPassword);
}

function upgrade_password_if_needed(string $username, string $plainPassword, string $storedPassword): void
{
    $info = password_get_info($storedPassword);

    if (!empty($info["algo"])) {
        return;
    }

    if (!hash_equals($storedPassword, $plainPassword)) {
        return;
    }

    $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = db()->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $newHash, $username);
    $stmt->execute();
    $stmt->close();
}

function find_user_by_username(string $username): ?array
{
    $stmt = db()->prepare("SELECT id, name, username, password, login_date FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $user;
}

function body_class(string $page): string
{
    return $page === "auth" ? "page-auth" : "page-dashboard";
}
