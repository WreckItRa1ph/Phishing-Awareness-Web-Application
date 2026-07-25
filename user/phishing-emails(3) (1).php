<?php
session_start();

require_once "../config/db.php";
require_once "../functions/functions.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

$user = getUserByID($pdo, (int) $_SESSION["user_id"]);
$emails = getPhishingAssignments($pdo, (int) $_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phishing Inbox | PhishAware</title>
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
            <a href="phishing-emails.php"><i class="fas fa-envelope"></i> | Phishing Inbox</a>
            <a href="phishing-results.php"><i class="fas fa-chart-line"></i> | Phishing Results</a>
            <a href="training.php"><i class="fas fa-graduation-cap"></i> | Training</a>
            <a href="results.php"><i class="fas fa-chart-line"></i> | Results</a>
            <a href="badges.php"><i class="fas fa-trophy"></i> | Badges</a>
            <a href="profile.php"><i class="fas fa-user"></i> | My Profile</a>
            <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> | Log Out</a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="topbar-left">
                <span>
                    Welcome,
                    <?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?>
                </span>
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
                <h1>Phishing Simulation Inbox</h1>
                <p>Review each simulated email and decide whether it should be opened, reported, or ignored.</p>
            </div>

            <div class="phishing-inbox">
                <?php if (empty($emails)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h2>No simulations assigned</h2>
                        <p>No phishing email simulations have been assigned to your account yet.</p>
                    </div>
                <?php else: ?>
                    <div class="phishing-inbox-header">
                        <span>Sender</span>
                        <span>Subject</span>
                        <span>Status</span>
                    </div>

                    <?php foreach ($emails as $email): ?>
                        <?php
                        $status = $email["status"] ?? "unopened";
                        $rowClass = $status === "unopened" ? "unread" : "read";
                        $statusClass = $status === "unopened" ? "new" : $status;
                        $statusLabel = $status === "unopened" ? "New" : ucfirst($status);
                        ?>

                        <a class="phishing-email-row <?= $rowClass ?>"
                           href="phishing-email-view.php?id=<?= (int) $email["assignment_id"] ?>">
                            <span class="phishing-sender">
                                <i class="fas fa-envelope"></i>
                                <span>
                                    <?= htmlspecialchars($email["sender_name"]) ?><br>
                                    <small><?= htmlspecialchars($email["sender_email"]) ?></small>
                                </span>
                            </span>

                            <span class="phishing-subject">
                                <?= htmlspecialchars($email["subject"]) ?>
                                <?php if (!empty($email["preview_text"])): ?>
                                    — <?= htmlspecialchars($email["preview_text"]) ?>
                                <?php endif; ?>
                            </span>

                            <span class="phishing-status">
                                <span class="status-badge status-<?= htmlspecialchars($statusClass) ?>">
                                    <?= htmlspecialchars($statusLabel) ?>
                                </span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="../js/scripts.js"></script>
</body>
</html>
