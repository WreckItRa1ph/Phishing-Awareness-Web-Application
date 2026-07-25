<?php
session_start();

require_once "../config/db.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$assignmentId = filter_input(INPUT_POST, "assignment_id", FILTER_VALIDATE_INT);
$action = $_POST["action"] ?? "";
$csrfToken = $_POST["csrf_token"] ?? null;

if (!$assignmentId || !verifyCsrfToken($csrfToken)) {
    http_response_code(400);
    exit("Invalid request.");
}

if (!in_array($action, ["reported", "ignored"], true)) {
    http_response_code(400);
    exit("Invalid action.");
}

if (!getPhishingAssignment($pdo, $assignmentId, (int) $_SESSION["user_id"])) {
    http_response_code(404);
    exit("Simulation not found.");
}

updatePhishingAction($pdo, $assignmentId, (int) $_SESSION["user_id"], $action);

if ($action === "reported") {
    $_SESSION["phishing_feedback"] = [
        "type" => "success",
        "message" => "Correct choice. You reported the suspicious email instead of interacting with its link.",
    ];
} else {
    $_SESSION["phishing_feedback"] = [
        "type" => "neutral",
        "message" => "Ignoring a suspicious message prevents a click, but reporting it is better because it can protect other users too.",
    ];
}

header("Location: phishing-email-view.php?id=" . $assignmentId);
exit();
