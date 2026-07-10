<?php
// User registration
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
    $confirm = $_POST["confirm"] ?? "";

    if ($username === "" || strlen($username) > 50) {
        $errors[] = "Username is required and must be 50 characters or fewer.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!$errors) {
        // Make sure the username is not already taken.
        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $errors[] = "That username is already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `user` (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            header("Location: login.php?registered=1");
            exit();
        }
    }
}

$page_title = "Register";
require "includes/header.php";
?>
<h1>Register</h1>

<?php foreach ($errors as $err): ?>
    <p class="error"><?= e($err) ?></p>
<?php endforeach; ?>

<form method="post" action="register.php">
    <label>Username
        <input type="text" name="username" maxlength="50" required
               value="<?= e($_POST["username"] ?? "") ?>">
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <label>Confirm password
        <input type="password" name="confirm" required>
    </label>
    <button type="submit">Create account</button>
</form>

<p>Already have an account? <a href="login.php">Log in</a>.</p>

<?php require "includes/footer.php"; ?>
