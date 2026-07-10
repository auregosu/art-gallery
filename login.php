<?php
// Login page
require_once "includes/db.php";
require_once "includes/auth.php";

if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM `user` WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true); // prevent session fixation
        $_SESSION["user_id"] = (int) $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["is_admin"] = (int) $user["is_admin"];
        header("Location: index.php");
        exit();
    }

    $errors[] = "Invalid username or password.";
}

$page_title = "Log in";
require "includes/header.php";
?>
<h1>Log in</h1>

<?php if (isset($_GET["registered"])): ?>
    <p class="notice">Account created. You can now log in.</p>
<?php endif; ?>

<?php foreach ($errors as $err): ?>
    <p class="error"><?= e($err) ?></p>
<?php endforeach; ?>

<form method="post" action="login.php">
    <label>Username
        <input type="text" name="username" required>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button type="submit">Log in</button>
</form>

<p>No account yet? <a href="register.php">Register</a>.</p>

<?php require "includes/footer.php"; ?>
