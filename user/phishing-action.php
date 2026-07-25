<?php

session_start();

require_once "../config/db.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$userId = (int) $_SESSION["user_id"];

$attemptId = filter_input(
    INPUT_POST,
    "attempt_id",
    FILTER_VALIDATE_INT
);

$scenarioId = filter_input(
    INPUT_POST,
    "scenario_id",
    FILTER_VALIDATE_INT
);

$choiceId = filter_input(
    INPUT_POST,
    "choice_id",
    FILTER_VALIDATE_INT
);

$csrfToken = $_POST["csrf_token"] ?? null;

if (
    !$attemptId
    || !$scenarioId
    || !$choiceId
    || !verifyCsrfToken($csrfToken)
) {
    http_response_code(400);
    exit("Invalid request.");
}

$attempt = getPhishingAttempt(
    $pdo,
    $attemptId,
    $userId
);

if ($attempt === false) {
    http_response_code(404);
    exit("Phishing attempt not found.");
}

if ((int) $attempt["scenario_id"] !== $scenarioId) {
    http_response_code(400);
    exit("The submitted scenario does not match this attempt.");
}

$result = completePhishingAttempt(
    $pdo,
    $attemptId,
    $userId,
    $choiceId
);

if ($result === false) {
    http_response_code(400);
    exit("The response could not be submitted.");
}

$_SESSION["phishing_feedback"] = [
    "attempt_id" => (int) $result["attempt_id"],
    "scenario_id" => (int) $result["scenario_id"],
    "choice_text" => $result["choice_text"],
    "is_correct" => (bool) $result["is_correct"],
    "score" => (int) $result["score"],
    "feedback" => $result["feedback"],
    "error_type" => $result["error_type"],
];

header(
    "Location: phishing-feedback.php?attempt_id="
    . (int) $attemptId
);

exit();
