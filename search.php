<?php
// Search for paintings or artists
require_once "includes/db.php";
require_once "includes/auth.php";

$q = trim($_GET["q"] ?? "");
$type = $_GET["type"] ?? "painting";
if (!in_array($type, ["painting", "artist"], true)) {
    $type = "painting";
}

$results = [];
if ($type === "artist") {
    if ($q !== "") {
        $like = "%" . $q . "%";
        $stmt = $pdo->prepare(
            'SELECT id, name FROM artist
                WHERE name LIKE ? OR description LIKE ?
                ORDER BY name',
        );
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query(
            'SELECT id, name FROM artist
                ORDER BY name',
        );
    }
} else {
    if ($q !== "") {
        $like = "%" . $q . "%";
        $stmt = $pdo->prepare(
            'SELECT p.id, p.title, p.image_path, a.name AS artist_name
            FROM painting p
            JOIN artist a ON a.id = p.artist_id
            WHERE p.title LIKE ? OR p.description LIKE ?
            ORDER BY p.title',
        );
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query(
            'SELECT p.id, p.title, p.image_path, a.name AS artist_name
            FROM painting p
            JOIN artist a ON a.id = p.artist_id
            ORDER BY p.title',
        );
    }
}
$results = $stmt->fetchAll();

$page_title = "Search";
require "includes/header.php";
?>
<h1>Search</h1>

<form method="get" action="search.php" class="search-form">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search...">
    <label><input type="radio" name="type" value="painting"
        <?= $type === "painting" ? "checked" : "" ?>> Paintings</label>
    <label><input type="radio" name="type" value="artist"
        <?= $type === "artist" ? "checked" : "" ?>> Artists</label>
    <button type="submit">Search</button>
</form>

<?php if ($q !== ""): ?>
    <h2>Results for &ldquo;<?= e($q) ?>&rdquo;</h2>
<?php else: ?>
    <h2>All results for <?= e($type) ?>s</h2>
<?php endif; ?>

<?php if (!$results): ?>
    <p>Nothing found.</p>
<?php elseif ($type === "artist"): ?>
    <ul class="result-list">
        <?php foreach ($results as $r): ?>
            <li><a href="artist.php?id=<?= (int) $r["id"] ?>"><?= e($r["name"]) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="gallery">
        <?php foreach ($results as $r): ?>
            <a class="thumb" href="painting.php?id=<?= (int) $r["id"] ?>">
                <img src="<?= PAINTINGS . e($r["image_path"]) ?>" alt="<?= e($r["title"]) ?>">
                <span><?= e($r["title"]) ?> &mdash; <?= e($r["artist_name"]) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require "includes/footer.php"; ?>
