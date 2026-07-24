<?php

session_start();

require_once("../config/db.php");
require_once("../functions/functions.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = getUserByID($pdo, $_SESSION["user_id"]);

?>


<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
</head>
<body>

<h1>
    <?= htmlspecialchars($user["first_name"]) ?>
    <?= htmlspecialchars($user["last_name"]) ?>
</h1>

<p>Email: <?= htmlspecialchars($user["email"]) ?></p>

<p>Role: <?= htmlspecialchars($user["role"]) ?></p>

<p>Member Since:
    <?= date("F j, Y", strtotime($user["created_at"])) ?>
</p>

</body>
</html>