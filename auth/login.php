<?php

session_start();

require_once "../config/db.php";

$message = "";
$message_type = "error";

// Show success message after registration
if (isset($_GET["registered"])) {
    $message = "Account created successfully! Please log in.";
    $message_type = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = :email";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":email" => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password_hash"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["first_name"] = $user["first_name"];
        $_SESSION["role"] = $user["role"];

        header("Location: ../user/home.php");
        exit();

    } else {

        $message = "Invalid email or password.";
        $message_type = "error";

    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PhishAware</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body class="auth-body">

<div class="auth-card">

    <div class="auth-logo">PhishAware</div>

    <h1>Welcome Back</h1>
    <p>Log in to continue your phishing awareness training.</p>

    <?php if (!empty($message)): ?>
        <div class="<?php echo $message_type === "success" ? "auth-message" : "auth-error"; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="auth-form">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>

    <div class="auth-link">
        Don't have an account?
        <a href="register.php">Create one</a>
    </div>

</div>

</body>
</html>