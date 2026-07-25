<?php

session_start();

require_once "../config/db.php";
require_once "../functions/functions.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

$userId = (int) $_SESSION["user_id"];

$attemptId = filter_input(
    INPUT_GET,
    "attempt_id",
    FILTER_VALIDATE_INT
);

if (!$attemptId) {
    http_response_code(400);
    exit("Invalid phishing attempt ID.");
}

$result = getPhishingAttemptResult(
    $pdo,
    $attemptId,
    $userId
);

if ($result === false) {
    http_response_code(404);
    exit("Phishing attempt not found.");
}

if ($result["status"] !== "completed") {
    header(
        "Location: phishing-email-view.php?id="
        . (int) $result["scenario_id"]
    );
    exit();
}

$user = getUserByID(
    $pdo,
    $userId
);

$isCorrect = (int) $result["is_correct"] === 1;
$score = (int) $result["score"];

$feedbackMessage = !empty($result["feedback"])
    ? $result["feedback"]
    : (
        $isCorrect
            ? "You selected the safest response."
            : "Review the warning signs and try to identify the safest response next time."
    );

$explanation = !empty($result["explanation"])
    ? $result["explanation"]
    : "No additional explanation is available for this scenario.";

$redFlags = !empty($result["red_flags"])
    ? $result["red_flags"]
    : "No warning signs have been listed for this scenario.";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Phishing Feedback | PhishAware</title>

    <link
        rel="stylesheet"
        href="../css/styles.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >
</head>

<body>

<div class="layout">

    <aside class="sidebar">

        <div class="logo">

            <img
                src="../images/placeholder_logo_1.png"
                alt="PhishAware Logo"
            >

            <span>| PhishAware</span>

        </div>

        <nav>

            <a href="user-dashboard.php">
                <i class="fas fa-home"></i>
                | My Dashboard
            </a>

            <a href="training.php">
                <i class="fas fa-graduation-cap"></i>
                | Training
            </a>

            <a href="phishing-emails.php">
                <i class="fas fa-envelope"></i>
                | Phishing Simulation
            </a>

            <a href="results.php">
                <i class="fas fa-chart-line"></i>
                | Results
            </a>

            <a href="badges.php">
                <i class="fas fa-trophy"></i>
                | Badges
            </a>

            <a href="profile.php">
                <i class="fas fa-user"></i>
                | My Profile
            </a>

            <a href="../auth/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                | Log Out
            </a>

        </nav>

    </aside>

    <div class="main">

        <header class="topbar">

            <div class="topbar-left">

                <span>
                    Welcome,
                    <?= htmlspecialchars(
                        $user["first_name"] . " " . $user["last_name"]
                    ) ?>
                </span>

            </div>

            <div class="topbar-right">

                <div class="profile-menu">

                    <img
                        src="../images/default_pfp.jpg"
                        alt="Profile"
                        class="avatar"
                        id="profile-btn"
                    >

                    <div
                        class="dropdown hidden"
                        id="profile-dropdown"
                    >

                        <a href="profile.php">
                            My Profile
                        </a>

                        <a href="../auth/logout.php">
                            Log Out
                        </a>

                    </div>

                </div>

            </div>

        </header>

        <main class="content">

            <div class="phishing-page-header">

                <h1>Phishing Simulation Feedback</h1>

                <p>
                    Review your response and the warning signs from this
                    simulated email.
                </p>

            </div>

            <div class="email-container">

                <div class="email-toolbar">

                    <a
                        class="email-back-link"
                        href="phishing-emails.php"
                    >
                        <i class="fas fa-arrow-left"></i>
                        Back to inbox
                    </a>

                    <span
                        class="status-badge <?= $isCorrect
                            ? "status-reported"
                            : "status-ignored" ?>"
                    >
                        <?= $isCorrect ? "Correct" : "Incorrect" ?>
                    </span>

                </div>

                <div class="email-header">

                    <h2>
                        <?= htmlspecialchars($result["title"]) ?>
                    </h2>

                    <div class="email-meta">

                        <span>
                            <strong>Subject:</strong>
                            <?= htmlspecialchars(
                                $result["subject_line"] ?? "No subject"
                            ) ?>
                        </span>

                        <span>
                            <strong>Sender:</strong>
                            <?= htmlspecialchars(
                                $result["sender_name"] ?? "Unknown Sender"
                            ) ?>

                            &lt;<?= htmlspecialchars(
                                $result["sender_email"] ?? "unknown@example.com"
                            ) ?>&gt;
                        </span>

                        <span>
                            <strong>Difficulty:</strong>
                            <?= htmlspecialchars(
                                ucfirst($result["difficulty"])
                            ) ?>
                        </span>

                    </div>

                </div>

                <div class="email-body">

                    <div class="feedback-result">

                        <h2>
                            <?php if ($isCorrect): ?>

                                <i class="fas fa-circle-check"></i>
                                Correct Response

                            <?php else: ?>

                                <i class="fas fa-circle-xmark"></i>
                                Incorrect Response

                            <?php endif; ?>
                        </h2>

                        <p>
                            <strong>Your score:</strong>
                            <?= $score ?>%
                        </p>

                        <p>
                            <strong>Your response:</strong>
                            <?= htmlspecialchars(
                                $result["choice_text"] ?? "No response recorded"
                            ) ?>
                        </p>

                        <?php if (!empty($result["error_type"])): ?>

                            <p>
                                <strong>Response issue:</strong>
                                <?= htmlspecialchars($result["error_type"]) ?>
                            </p>

                        <?php endif; ?>

                        <p>
                            <?= nl2br(
                                htmlspecialchars($feedbackMessage)
                            ) ?>
                        </p>

                    </div>

                </div>

            </div>

            <div
                class="warning-signs"
                style="margin-top: 20px;"
            >

                <h2>
                    <i class="fas fa-triangle-exclamation"></i>
                    Warning Signs
                </h2>

                <p>
                    <?= nl2br(
                        htmlspecialchars($redFlags)
                    ) ?>
                </p>

            </div>

            <div
                class="warning-signs"
                style="margin-top: 20px;"
            >

                <h2>
                    <i class="fas fa-lightbulb"></i>
                    Scenario Explanation
                </h2>

                <p>
                    <?= nl2br(
                        htmlspecialchars($explanation)
                    ) ?>
                </p>

            </div>

            <div
                class="email-actions"
                style="margin-top: 20px;"
            >

                <a
                    class="report-btn"
                    href="phishing-emails.php"
                >
                    <i class="fas fa-envelope"></i>
                    Return to Phishing Inbox
                </a>

                <a
                    class="ignore-btn"
                    href="phishing-email-view.php?id=<?= (int) $result["scenario_id"] ?>"
                >
                    <i class="fas fa-rotate-right"></i>
                    Try Scenario Again
                </a>

            </div>

        </main>

    </div>

</div>

<script src="../js/scripts.js"></script>

</body>
</html>
