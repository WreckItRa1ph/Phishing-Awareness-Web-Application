<?php

/**
 * Redirect visitors who are not logged in.
 */
function requireLoggedInUser(): void
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

/**
 * Create or return the current CSRF token.
 */
function getCsrfToken(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

/**
 * Verify a submitted CSRF token.
 */
function verifyCsrfToken(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], $token);
}

/**
 * Get all phishing scenarios.
 *
 * The most recent attempt made by the current user is included when one
 * exists. This allows the phishing inbox page to show whether a scenario
 * has been started or completed.
 */
function getPhishingScenarios(PDO $pdo, int $userId): array
{
    $sql = "SELECT ps.id,
                   ps.title,
                   ps.scenario_type,
                   ps.difficulty,
                   ps.sender_name,
                   ps.sender_email,
                   ps.subject_line,
                   ps.created_at,
                   ua.id AS attempt_id,
                   ua.score,
                   ua.status AS attempt_status,
                   ua.started_at,
                   ua.completed_at
            FROM phishing_scenarios ps
            LEFT JOIN user_attempts ua
                ON ua.id = (
                    SELECT ua2.id
                    FROM user_attempts ua2
                    WHERE ua2.scenario_id = ps.id
                      AND ua2.user_id = :user_id
                    ORDER BY ua2.id DESC
                    LIMIT 1
                )
            ORDER BY ps.created_at DESC, ps.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_id" => $userId,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get one phishing scenario.
 */
function getPhishingScenario(PDO $pdo, int $scenarioId): array|false
{
    $sql = "SELECT id,
                   title,
                   scenario_type,
                   difficulty,
                   sender_name,
                   sender_email,
                   subject_line,
                   email_body,
                   red_flags,
                   explanation,
                   created_at
            FROM phishing_scenarios
            WHERE id = :scenario_id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":scenario_id" => $scenarioId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all response choices for one scenario.
 *
 * The is_correct field is intentionally not returned here so the correct
 * answer is not exposed before the user submits a response.
 */
function getScenarioChoices(PDO $pdo, int $scenarioId): array
{
    $sql = "SELECT id,
                   scenario_id,
                   choice_text
            FROM scenario_choices
            WHERE scenario_id = :scenario_id
            ORDER BY id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":scenario_id" => $scenarioId,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Start a new attempt for a phishing scenario.
 */
function startPhishingAttempt(
    PDO $pdo,
    int $userId,
    int $scenarioId
): int {
    $sql = "INSERT INTO user_attempts (
                user_id,
                scenario_id,
                score,
                status
            )
            VALUES (
                :user_id,
                :scenario_id,
                0,
                'started'
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_id" => $userId,
        ":scenario_id" => $scenarioId,
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Get an attempt belonging to the current user.
 */
function getPhishingAttempt(
    PDO $pdo,
    int $attemptId,
    int $userId
): array|false {
    $sql = "SELECT id,
                   user_id,
                   scenario_id,
                   selected_choice_id,
                   score,
                   status,
                   started_at,
                   completed_at
            FROM user_attempts
            WHERE id = :attempt_id
              AND user_id = :user_id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":attempt_id" => $attemptId,
        ":user_id" => $userId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Return an unfinished attempt for a scenario when one exists.
 */
function getStartedPhishingAttempt(
    PDO $pdo,
    int $userId,
    int $scenarioId
): array|false {
    $sql = "SELECT id,
                   user_id,
                   scenario_id,
                   selected_choice_id,
                   score,
                   status,
                   started_at,
                   completed_at
            FROM user_attempts
            WHERE user_id = :user_id
              AND scenario_id = :scenario_id
              AND status = 'started'
            ORDER BY id DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_id" => $userId,
        ":scenario_id" => $scenarioId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Start a scenario or return the user's existing unfinished attempt.
 */
function getOrCreatePhishingAttempt(
    PDO $pdo,
    int $userId,
    int $scenarioId
): int {
    $existingAttempt = getStartedPhishingAttempt(
        $pdo,
        $userId,
        $scenarioId
    );

    if ($existingAttempt !== false) {
        return (int) $existingAttempt["id"];
    }

    return startPhishingAttempt(
        $pdo,
        $userId,
        $scenarioId
    );
}

/**
 * Complete an attempt and return the result.
 *
 * A correct answer earns 100 points. An incorrect answer earns 0 points.
 */
function completePhishingAttempt(
    PDO $pdo,
    int $attemptId,
    int $userId,
    int $choiceId
): array|false {
    $attempt = getPhishingAttempt(
        $pdo,
        $attemptId,
        $userId
    );

    if ($attempt === false || $attempt["status"] !== "started") {
        return false;
    }

    $sql = "SELECT id,
                   scenario_id,
                   choice_text,
                   is_correct,
                   feedback,
                   error_type
            FROM scenario_choices
            WHERE id = :choice_id
              AND scenario_id = :scenario_id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":choice_id" => $choiceId,
        ":scenario_id" => (int) $attempt["scenario_id"],
    ]);

    $choice = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($choice === false) {
        return false;
    }

    $isCorrect = (int) $choice["is_correct"] === 1;
    $score = $isCorrect ? 100 : 0;

    $sql = "UPDATE user_attempts
            SET selected_choice_id = :choice_id,
                score = :score,
                status = 'completed',
                completed_at = NOW()
            WHERE id = :attempt_id
              AND user_id = :user_id
              AND status = 'started'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":choice_id" => $choiceId,
        ":score" => $score,
        ":attempt_id" => $attemptId,
        ":user_id" => $userId,
    ]);

    if ($stmt->rowCount() === 0) {
        return false;
    }

    return [
        "attempt_id" => $attemptId,
        "scenario_id" => (int) $attempt["scenario_id"],
        "choice_id" => $choiceId,
        "choice_text" => $choice["choice_text"],
        "is_correct" => $isCorrect,
        "score" => $score,
        "feedback" => $choice["feedback"],
        "error_type" => $choice["error_type"],
    ];
}

/**
 * Get the completed result and educational feedback for one attempt.
 */
function getPhishingAttemptResult(
    PDO $pdo,
    int $attemptId,
    int $userId
): array|false {
    $sql = "SELECT ua.id AS attempt_id,
                   ua.scenario_id,
                   ua.selected_choice_id,
                   ua.score,
                   ua.status,
                   ua.started_at,
                   ua.completed_at,
                   ps.title,
                   ps.scenario_type,
                   ps.difficulty,
                   ps.sender_name,
                   ps.sender_email,
                   ps.subject_line,
                   ps.red_flags,
                   ps.explanation,
                   sc.choice_text,
                   sc.is_correct,
                   sc.feedback,
                   sc.error_type
            FROM user_attempts ua
            INNER JOIN phishing_scenarios ps
                ON ps.id = ua.scenario_id
            LEFT JOIN scenario_choices sc
                ON sc.id = ua.selected_choice_id
            WHERE ua.id = :attempt_id
              AND ua.user_id = :user_id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":attempt_id" => $attemptId,
        ":user_id" => $userId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get summary information for the current user's phishing attempts.
 */
function getPhishingSummary(PDO $pdo, int $userId): array
{
    $sql = "SELECT
                COUNT(*) AS total_attempts,
                SUM(status = 'started') AS started,
                SUM(status = 'completed') AS completed,
                SUM(
                    status = 'completed' AND score > 0
                ) AS correct,
                SUM(
                    status = 'completed' AND score = 0
                ) AS incorrect,
                COALESCE(
                    ROUND(
                        AVG(
                            CASE
                                WHEN status = 'completed' THEN score
                                ELSE NULL
                            END
                        )
                    ),
                    0
                ) AS average_score
            FROM user_attempts
            WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_id" => $userId,
    ]);

    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($summary === false) {
        return [
            "total_attempts" => 0,
            "started" => 0,
            "completed" => 0,
            "correct" => 0,
            "incorrect" => 0,
            "average_score" => 0,
        ];
    }

    foreach ($summary as $key => $value) {
        $summary[$key] = (int) ($value ?? 0);
    }

    return $summary;
}
