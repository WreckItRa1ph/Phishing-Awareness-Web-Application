<?php
session_start();

require_once "../config/db.php";
require_once "../functions/functions.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

$assignmentId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$assignmentId) {
    http_response_code(400);
    exit("Invalid simulation ID.");
}

$email = getPhishingAssignment($pdo, $assignmentId, (int) $_SESSION["user_id"]);
if (!$email) {
    http_response_code(404);
    exit("Simulation not found.");
}

markPhishingEmailOpened($pdo, $assignmentId, (int) $_SESSION["user_id"]);
$email = getPhishingAssignment($pdo, $assignmentId, (int) $_SESSION["user_id"]);
$user = getUserByID($pdo, (int) $_SESSION["user_id"]);
$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($email["subject"]) ?> | PhishAware</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">
            <img src="../images/placeholder_logo_1.png" alt="PhishAware Logo">
            <span>| PhishAware</span>
        </div>

        <nav>
            <a href="user-dashboard.php"><i class="fas fa-home"></i> | My Dashboard</a>
            <a href="training.php"><i class="fas fa-graduation-cap"></i> | Training</a>
            <a href="phishing-emails.php"><i class="fas fa-envelope"></i> | Phishing Simulation</a>
            <a href="results.php"><i class="fas fa-chart-line"></i> | Results</a>
            <a href="badges.php"><i class="fas fa-trophy"></i> | Badges</a>
            <a href="profile.php"><i class="fas fa-user"></i> | My Profile</a>
            <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> | Log Out</a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="topbar-left">
                <span>Welcome, <?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></span>
            </div>

            <div class="topbar-right">
                <div class="profile-menu">
                    <img src="../images/default_pfp.jpg" alt="Profile" class="avatar" id="profile-btn">
                    <div class="dropdown hidden" id="profile-dropdown">
                        <a href="profile.php">My Profile</a>
                        <a href="../auth/logout.php">Log Out</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="phishing-page-header">
                <h1>Simulated Email</h1>
                <p>Review this message carefully and choose how you would respond.</p>
            </div>

            <div class="email-container">
                <div class="email-toolbar">
                    <a class="email-back-link" href="phishing-emails.php">
                        <i class="fas fa-arrow-left"></i> Back to inbox
                    </a>
                    <span class="status-badge status-<?= htmlspecialchars($email["status"] === "unopened" ? "new" : $email["status"]) ?>">
                        <?= htmlspecialchars($email["status"] === "unopened" ? "New" : ucfirst($email["status"])) ?>
                    </span>
                </div>

                <div class="email-header">
                    <h2><?= htmlspecialchars($email["subject"]) ?></h2>
                    <div class="email-meta">
                        <span><strong>From:</strong> <?= htmlspecialchars($email["sender_name"]) ?> &lt;<?= htmlspecialchars($email["sender_email"]) ?>&gt;</span>
                        <span><strong>To:</strong> <?= htmlspecialchars($user["email"]) ?></span>
                    </div>
                </div>

                <div class="email-body">
                    <?= $email["body_html"] ?>

                    <a class="simulated-email-link" href="track-phishing-click.php?id=<?= (int) $email["assignment_id"] ?>">
                        <?= htmlspecialchars($email["link_label"]) ?>
                    </a>
                </div>

                <div class="email-actions">
                    <form method="POST" action="phishing-action.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="assignment_id" value="<?= (int) $email["assignment_id"] ?>">
                        <input type="hidden" name="action" value="reported">
                        <button class="report-btn" type="submit">
                            <i class="fas fa-flag"></i> Report Phishing
                        </button>
                    </form>

                    <form method="POST" action="phishing-action.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="assignment_id" value="<?= (int) $email["assignment_id"] ?>">
                        <input type="hidden" name="action" value="ignored">
                        <button class="ignore-btn" type="submit">
                            <i class="fas fa-trash"></i> Ignore / Delete
                        </button>
                    </form>
                </div>
            </div>

            <?php if (in_array($email["status"], ["clicked", "reported", "ignored"], true)): ?>
                <div class="warning-signs" style="margin-top: 20px;">
                    <h2>Warning signs in this email</h2>
                    <p><?= htmlspecialchars($email["red_flags"]) ?></p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="../js/scripts.js"></script>
</body>
</html>
