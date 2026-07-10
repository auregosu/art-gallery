<?php
// Header of every page.
// Expects $page_title to be set beforehand.
require_once "auth.php";
$page_title = $page_title ?? "Art Gallery";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> &middot; Art Gallery</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<!-- Animated background -->
<canvas id="bg-canvas"></canvas>
<script src="assets/js/background.js" defer></script>

<header class="site-header">
    <a class="logo" href="index.php">
        <img src="<?= ASSETS . "logo.png" ?>">
        Connoisseur's Art Gallery
    </a>
    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search</a>
        <?php if (is_logged_in()): ?>
            <a href="favourites.php">Favourites</a>
            <?php if (is_admin()): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>
            <a href="scripts/logout.php">Logout (<?= e($_SESSION["username"]) ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
