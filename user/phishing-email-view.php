<?php

session_start();

require_once "../config/db.php";
require_once "../functions/functions.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

$userId = (int) $_SESSION["user_id"];

$scenarioId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$scenarioId) {
    http_response_code(400);
    exit("Invalid phishing scenario ID.");
}

$scenario = getPhishingScenario(
    $pdo,
    $scenarioId
);

if ($scenario === false) {
    http_response_code(404);
    exit("Phishing scenario not found.");
}

$choices = getScenarioChoices(
    $pdo,
    $scenarioId
);

$attemptId = getOrCreatePhishingAttempt(
    $pdo,
    $userId,
    $scenarioId
);

$user = getUserByID(
    $pdo,
    $userId
);

$csrfToken = getCsrfToken();

$senderName = !empty($scenario["sender_name"])
    ? $scenario["sender_name"]
    : "Unknown Sender";

$senderEmail = !empty($scenario["sender_email"])
    ? $scenario["sender_email"]
    : "unknown@example.com";

$subjectLine = !empty($scenario["subject_line"])
    ? $scenario["subject_line"]
    : "No subject";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($subjectLine) ?> | PhishAware
    </title>

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

                <h1>Simulated Email</h1>

                <p>
                    Review the message carefully, then select the response
                    you believe is safest.
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

                    <span class="status-badge status-opened">
                        In Progress
                    </span>

                </div>

                <div class="email-header">

                    <h2>
                        <?= htmlspecialchars($subjectLine) ?>
                    </h2>

                    <div class="email-meta">

                        <span>
                            <strong>From:</strong>
                            <?= htmlspecialchars($senderName) ?>
                            &lt;<?= htmlspecialchars($senderEmail) ?>&gt;
                        </span>

                        <span>
                            <strong>To:</strong>
                            <?= htmlspecialchars($user["email"]) ?>
                        </span>

                        <span>
                            <strong>Difficulty:</strong>
                            <?= htmlspecialchars(
                                ucfirst($scenario["difficulty"])
                            ) ?>
                        </span>

                    </div>

                </div>

                <div class="email-body">

                    <?= nl2br(
                        htmlspecialchars(
                            $scenario["email_body"]
                        )
                    ) ?>

                </div>

                <div class="email-actions">

                    <?php if (empty($choices)): ?>

                        <div class="empty-state">

                            <i class="fas fa-triangle-exclamation"></i>

                            <h2>No response choices available</h2>

                            <p>
                                This scenario does not have any response
                                choices in the database yet.
                            </p>

                        </div>

                    <?php else: ?>

                        <form
                            method="POST"
                            action="phishing-action.php"
                            class="phishing-choice-form"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken) ?>"
                            >

                            <input
                                type="hidden"
                                name="attempt_id"
                                value="<?= (int) $attemptId ?>"
                            >

                            <input
                                type="hidden"
                                name="scenario_id"
                                value="<?= (int) $scenarioId ?>"
                            >

                            <fieldset>

                                <legend>
                                    What would you do with this email?
                                </legend>

                                <?php foreach ($choices as $choice): ?>

                                    <label class="phishing-choice">

                                        <input
                                            type="radio"
                                            name="choice_id"
                                            value="<?= (int) $choice["id"] ?>"
                                            required
                                        >

                                        <span>
                                            <?= htmlspecialchars(
                                                $choice["choice_text"]
                                            ) ?>
                                        </span>

                                    </label>

                                <?php endforeach; ?>

                            </fieldset>

                            <button
                                class="report-btn"
                                type="submit"
                            >
                                <i class="fas fa-check"></i>
                                Submit Response
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        </main>

    </div>

</div>

<script src="../js/scripts.js"></script>

</body>
</html>
