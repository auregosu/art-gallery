<?php
// Defines small auth/output helper functions.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION["user_id"]);
}

function is_admin(): bool
{
    return is_logged_in() && !empty($_SESSION["is_admin"]);
}

function current_user_id(): ?int
{
    return $_SESSION["user_id"] ?? null;
}

// Redirect to the login page if the visitor is not logged in.
function require_login(): void
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Block access for anyone who is not an administrator.
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit("Access denied: administrators only.");
    }
}

// Escape a string for safe output inside HTML.
function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
