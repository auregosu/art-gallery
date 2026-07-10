<?php
// Main page, shows the most recently added paintings.
require_once "includes/db.php";
require_once "includes/auth.php";

$stmt = $pdo->query(
    'SELECT p.id, p.title, p.image_path, a.name AS artist_name
     FROM painting p
     JOIN artist a ON a.id = p.artist_id
     ORDER BY p.date_added DESC
     LIMIT 8',
);
$recent = $stmt->fetchAll();

$page_title = "Home";
require "includes/header.php";
?>
<h1>Welcome to the connoisseur's art gallery...</h1>

<h2>Recently added works:</h2>

<?php if (!$recent): ?>
    <p>No paintings have been added yet.</p>
<?php else: ?>
    <div class="slideshow" id="slideshow">
        <div class="slides-track" id="slidesTrack">
            <?php foreach ($recent as $i => $p): ?>
                <a class="slide"
                href="painting.php?id=<?= (int) $p["id"] ?>">
                    <img src="<?= e(PAINTINGS . $p["image_path"]) ?>" alt="<?= e($p["title"]) ?>">
                    <span class="caption">
                        <?= e($p["title"]) ?> &mdash; <?= e($p["artist_name"]) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="nav prev" id="prevSlide" type="button">&#10094;</button>
        <button class="nav next" id="nextSlide" type="button">&#10095;</button>
    </div>
    <script src="assets/js/slideshow.js"></script>
<?php endif; ?>

<?php require "includes/footer.php"; ?>
