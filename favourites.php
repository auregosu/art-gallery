<?php
// The user's favourite paintings.
require_once "includes/db.php";
require_once "includes/auth.php";
require_login();

$stmt = $pdo->prepare(
    'SELECT p.id, p.title, p.image_path, a.name AS artist_name
     FROM favourite f
     JOIN painting p ON p.id = f.painting_id
     JOIN artist a ON a.id = p.artist_id
     WHERE f.user_id = ?
     ORDER BY f.added_at DESC',
);
$stmt->execute([current_user_id()]);
$favourites = $stmt->fetchAll();

$page_title = "My favourites";
require "includes/header.php";
?>
<h1>My favourites</h1>

<?php if (!$favourites): ?>
    <p>You have no favourite works yet.</p>
<?php else: ?>
    <div class="gallery">
        <?php foreach ($favourites as $f): ?>
            <a class="thumb" href="painting.php?id=<?= (int) $f["id"] ?>">
                <img src="<?= PAINTINGS . e($f["image_path"]) ?>" alt="<?= e($f["title"]) ?>">
                <span><?= e($f["title"]) ?> &mdash; <?= e($f["artist_name"]) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require "includes/footer.php"; ?>
