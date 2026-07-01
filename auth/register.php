<?php

require_once "../config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {

        $message = "All fields are required.";
        $message_type = "error";

    } else {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, password_hash)
                VALUES (:first_name, :last_name, :email, :password_hash)";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                ":first_name" => $first_name,
                ":last_name"  => $last_name,
                ":email"      => $email,
                ":password_hash" => $password_hash
            ]);

            // Registration successful - send user to login page
            header("Location: login.php?registered=1");
            exit();

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $message = "An account with that email already exists.";
            } else {
                $message = "Registration failed.";
            }

            $message_type = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | PhishAware</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body class="auth-body">

<div class="auth-card">

    <div class="auth-logo">PhishAware</div>

    <h1>Create an Account</h1>
    <p>Sign up to begin your phishing awareness training.</p>

    <?php if (!empty($message)): ?>
        <div class="<?php echo $message_type === "success" ? "auth-message" : "auth-error"; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="auth-form">

        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Register</button>

    </form>

    <div class="auth-link">
        Already have an account?
        <a href="login.php">Log in</a>
    </div>

</div>

</body>
</html>