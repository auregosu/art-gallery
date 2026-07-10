<?php
// Ends the session and returns to the home page.
require_once "../includes/auth.php";

$_SESSION = [];
session_destroy();

header("Location: ../index.php");
exit();
