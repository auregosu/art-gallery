<?php
// Artist details and list of works
require_once "includes/db.php";
require_once "includes/auth.php";

$id = (int) ($_GET["id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_admin();

    if (isset($_POST["delete"])) {
        $stmt = $pdo->prepare("SELECT portrait_path FROM artist WHERE id = ?");
        $stmt->execute([$id]);
        $imageFile = $stmt->fetchColumn();

        try {
            $pdo->prepare("DELETE FROM artist WHERE id = ?")->execute([$id]);
            if ($imageFile) {
                $path = PORTRAITS . $imageFile;
                unlink($path);
            }
            header("Location: search.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error deleting artist: There may be paintings associated with this artist.";
        }

    }
}

$stmt = $pdo->prepare("SELECT * FROM artist WHERE id = ?");
$stmt->execute([$id]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    $page_title = "Not found";
    require "includes/header.php";
    echo "<h1>Artist not found</h1>";
    require "includes/footer.php";
    exit();
}

$stmt = $pdo->prepare(
    'SELECT id, title, image_path FROM painting
     WHERE artist_id = ?
     ORDER BY date_painted',
);
$stmt->execute([$id]);
$works = $stmt->fetchAll();

$page_title = $artist["name"];
require "includes/header.php";
?>
<article class="artist">
    <?php if ($artist["portrait_path"]): ?>
        <img class="portrait" src="<?= PORTRAITS . e($artist["portrait_path"]) ?>"
             alt="<?= e($artist["name"]) ?>">
    <?php endif; ?>
    <h1><?= e($artist["name"]) ?></h1>
    <p class="meta">
        <?php if ($artist["birthdate"]): ?>
            <?= e($artist["birthdate"]) ?>
        <?php endif; ?>
        <?php if ($artist["death"]): ?>
            &ndash; <?= e($artist["death"]) ?>
        <?php endif; ?>
    </p>
    <?php if ($artist["description"]): ?>
        <p><?= nl2br(e($artist["description"])) ?></p>
    <?php endif; ?>

    <?php if (is_admin()): ?>
        <!-- Life would be better if forms supported method='delete' -->
        <form method="post" action="artist.php?id=<?= $id ?>">
            <button class="admin" type="submit" name="delete" value="1">
                REMOVE ARTIST FROM DATABASE
            </button>
        </form>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?><p class="error"><?= e($err) ?></p><?php endforeach; ?>
</article>

<section class="works">
    <h2>Works</h2>
    <?php if (!$works): ?>
        <p>No works listed for this artist.</p>
    <?php else: ?>
        <div class="gallery">
            <?php foreach ($works as $w): ?>
                <a class="thumb" href="painting.php?id=<?= (int) $w["id"] ?>">
                    <img src="<?= PAINTINGS . e($w["image_path"]) ?>" alt="<?= e($w["title"]) ?>">
                    <span><?= e($w["title"]) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require "includes/footer.php"; ?>