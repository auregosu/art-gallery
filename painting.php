<?php
// Single painting, users can favourite it and leave comments.
require_once "includes/db.php";
require_once "includes/auth.php";

$id = (int) ($_GET["id"] ?? 0);

// Favourite toggle / Comment
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_login();
    $uid = current_user_id();

    if (isset($_POST["toggle_favourite"])) {
        $stmt = $pdo->prepare("SELECT 1 FROM favourite WHERE user_id = ? AND painting_id = ?");
        $stmt->execute([$uid, $id]);

        if ($stmt->fetch()) {
            $pdo->prepare("DELETE FROM favourite WHERE user_id = ? AND painting_id = ?")->execute([
                $uid,
                $id,
            ]);
        } else {
            $pdo->prepare("INSERT INTO favourite (user_id, painting_id) VALUES (?, ?)")->execute([
                $uid,
                $id,
            ]);
        }
    } elseif (isset($_POST["comment"])) {
        $content = trim($_POST["comment"]);
        if ($content !== "") {
            $pdo->prepare(
                "INSERT INTO comment (painting_id, user_id, content) VALUES (?, ?, ?)",
            )->execute([$id, $uid, $content]);
        }
    } elseif (isset($_POST["delete"])) {
        // Delete the image and the painting's record in the database
        $stmt = $pdo->prepare("SELECT image_path FROM painting WHERE id = ?");
        $stmt->execute([$id]);
        $imageFile = $stmt->fetchColumn();

        $pdo->prepare("DELETE FROM painting WHERE id = ?")->execute([$id]);

        if ($imageFile) {
            $path = PAINTINGS . $imageFile;
            unlink($path);
        }
        header("Location: search.php");
        exit();
    }

    // Redirect after POST so a refresh doesn't resubmit.
    header("Location: painting.php?id=" . $id);
    exit();
}

// Load the painting
$stmt = $pdo->prepare(
    'SELECT p.*, a.name AS artist_name
     FROM painting p
     JOIN artist a ON a.id = p.artist_id
     WHERE p.id = ?',
);
$stmt->execute([$id]);
$painting = $stmt->fetch();

if (!$painting) {
    http_response_code(404);
    $page_title = "Not found";
    require "includes/header.php";
    echo "<h1>Painting not found</h1>";
    require "includes/footer.php";
    exit();
}

// Is the current user already favouriting this painting?
$is_fav = false;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT 1 FROM favourite WHERE user_id = ? AND painting_id = ?");
    $stmt->execute([current_user_id(), $id]);
    $is_fav = (bool) $stmt->fetch();
}

// Load comments.
$stmt = $pdo->prepare(
    'SELECT c.content, c.created_at, u.username
     FROM comment c
     JOIN `user` u ON u.id = c.user_id
     WHERE c.painting_id = ?
     ORDER BY c.created_at DESC',
);
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

$page_title = $painting["title"];
require "includes/header.php";
?>
<article class="painting">
    <img class="painting-image" id="paintingImage"
         src="<?= PAINTINGS . e($painting["image_path"]) ?>"
         alt="<?= e($painting["title"]) ?>">
    <div class="painting-detail">
        <h1 style="font-style: italic;"><?= e($painting["title"]) ?></h1>
        <p class="meta">
            by <a href="artist.php?id=<?= (int) $painting["artist_id"] ?>"><?= e(
    $painting["artist_name"],

) ?></a>
        <?php if ($painting["date_painted"]): ?>
            &middot; <?= e($painting["date_painted"]) ?>
        <?php endif; ?>
    </p>
    <?php if ($painting["description"]): ?>
        <p><?= nl2br(e($painting["description"])) ?></p>
    <?php endif; ?>

    <?php if (is_logged_in()): ?>
        <form method="post" action="painting.php?id=<?= $id ?>">
            <button type="submit" name="toggle_favourite" value="1">
                <?= $is_fav ? "Remove from favourites" : "Add to favourites" ?>
            </button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Log in</a> to add this work to your favourites.</p>
    <?php endif; ?>

    <?php if (is_admin()): ?>
        <!-- Life would be better if forms supported method='delete' -->
        <form method="post" action="painting.php?id=<?= $id ?>">
            <button class="admin" type="submit" name="delete" value="1">
                REMOVE PAINTING FROM DATABASE
            </button>
        </form>
    <?php endif; ?>
    </div>
</article>
<div class="lightbox" id="lightbox" aria-hidden="true">
    <img id="lightboxImg"
         src="<?= PAINTINGS . e($painting["image_path"]) ?>"
         alt="<?= e($painting["title"]) ?>">
</div>
<script src="assets/js/lightbox.js"></script>

<section class="comments">
    <h2>Comments</h2>

    <?php if (is_logged_in()): ?>
        <form method="post" action="painting.php?id=<?= $id ?>">
            <label>Add a comment
                <textarea name="comment" rows="3" required></textarea>
            </label>
            <button type="submit">Post comment</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Log in</a> to leave a comment.</p>
    <?php endif; ?>

    <?php if (!$comments): ?>
        <p>No comments yet.</p>
    <?php else: ?>
        <ul class="comment-list">
            <?php foreach ($comments as $c): ?>
                <li>
                    <strong><?= e($c["username"]) ?></strong>
                    <span class="meta"><?= e($c["created_at"]) ?></span>
                    <p><?= nl2br(e($c["content"])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php require "includes/footer.php"; ?>
