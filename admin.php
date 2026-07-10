<?php
// Admin page: Upload a new artist or a new painting.
require_once "includes/db.php";
require_once "includes/auth.php";
require_admin();

$messages = [];
$errors = [];

// Saves an uploaded image and returns its stored path (for <img src>), or null.
function handle_image_upload(string $field, array &$errors): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]["error"] === UPLOAD_ERR_NO_FILE) {
        return null; // nothing uploaded
    }

    $directory = PAINTINGS;
    if ($field == "portrait") {
        $directory = PORTRAITS;
    }

    $file = $_FILES[$field];
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed.";
        return null;
    }

    // Verify it really is an image and get its type.
    $info = getimagesize($file["tmp_name"]);
    if ($info === false) {
        $errors[] = "Uploaded file is not a valid image.";
        return null;
    }

    $extension = image_type_to_extension($info[2], false);
    $name = bin2hex(random_bytes(8)) . "." . $extension;

    // Create the portraits directory if it doesn't exist.
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    // Try moving the file.
    $dest = $directory . $name;
    if (!move_uploaded_file($file["tmp_name"], $dest)) {
        $errors[] = "Could not save the uploaded file to " . $dest;
        return null;
    }

    return $name;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_artist") {
        $name = trim($_POST["name"] ?? "");
        if ($name === "") {
            $errors[] = "Artist name is required.";
        }
        $portrait = handle_image_upload("portrait", $errors);

        if (!$errors) {
            $stmt = $pdo->prepare(
                'INSERT INTO artist (name, portrait_path, description, birthdate, death)
                 VALUES (?, ?, ?, ?, ?)',
            );
            $stmt->execute([
                $name,
                $portrait,
                trim($_POST["description"] ?? "") ?: null,
                $_POST["birthdate"] ?? "" ?: null,
                $_POST["death"] ?? "" ?: null,
            ]);
            $messages[] = "Artist added.";
        }
    } elseif ($action === "add_painting") {
        $title = trim($_POST["title"] ?? "");
        $artist_id = (int) ($_POST["artist_id"] ?? 0);

        if ($title === "") {
            $errors[] = "Painting title is required.";
        }
        if ($artist_id <= 0) {
            $errors[] = "Please choose an artist.";
        }

        $image = handle_image_upload("image", $errors);
        if (!$image) {
            $errors[] = "A painting image is required.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare(
                'INSERT INTO painting (title, image_path, description, artist_id, date_painted)
                 VALUES (?, ?, ?, ?, ?)',
            );
            $stmt->execute([
                $title,
                $image,
                trim($_POST["description"] ?? "") ?: null,
                $artist_id,
                $_POST["date_painted"] ?? "" ?: null,
            ]);
            $messages[] = "Painting added.";
        }
    }
}

// Artists for the painting form's dropdown.
$artists = $pdo->query("SELECT id, name FROM artist ORDER BY name")->fetchAll();

$page_title = "Admin";
require "includes/header.php";
?>
<h1>Admin</h1>

<?php foreach ($messages as $m): ?><p class="notice"><?= e($m) ?></p><?php endforeach; ?>
<?php foreach ($errors as $err): ?><p class="error"><?= e($err) ?></p><?php endforeach; ?>

<div class="admin-box">
    <section class="admin-block">
        <h2>Add artist</h2>
        <form method="post" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_artist">
            <label>Name <input type="text" name="name" maxlength="150" required></label>
            <label>Portrait image <input type="file" name="portrait" accept="image/*"></label>
            <label>Description <textarea name="description" rows="3"></textarea></label>
            <label>Born <input type="date" name="birthdate"></label>
            <label>Died <input type="date" name="death"></label>
            <button type="submit">Add artist</button>
        </form>
    </section>

    <section class="admin-block">
        <h2>Add painting</h2>
        <?php if (!$artists): ?>
            <p>Add an artist first.</p>
        <?php else: ?>
            <form method="post" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_painting">
                <label>Title <input type="text" name="title" maxlength="200" required></label>
                <label>Image <input type="file" name="image" accept="image/*" required></label>
                <label>Description <textarea name="description" rows="3"></textarea></label>
                <label>Artist
                    <select name="artist_id" required>
                        <option value="">-- choose --</option>
                        <?php foreach ($artists as $a): ?>
                            <option value="<?= (int) $a["id"] ?>"><?= e($a["name"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Date painted <input type="date" name="date_painted"></label>
                <button type="submit">Add painting</button>
            </form>
        <?php endif; ?>
    </section>
</div>

<?php require "includes/footer.php"; ?>
