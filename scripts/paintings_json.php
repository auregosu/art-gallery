<?php
// Returns a JSON array of painting image paths, used by the animated background
require_once "../includes/db.php";

header("Content-Type: application/json");

$paths = $pdo->query("SELECT image_path FROM painting")->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($paths);
