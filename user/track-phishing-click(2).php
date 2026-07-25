<?php
session_start();

require_once "../config/db.php";
require_once "../functions/phishing-functions.php";

requireLoggedInUser();

$assignmentId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$assignmentId || !getPhishingAssignment($pdo, $assignmentId, (int) $_SESSION["user_id"])) {
    http_response_code(404);
    exit("Simulation not found.");
}

updatePhishingAction($pdo, $assignmentId, (int) $_SESSION["user_id"], "clicked");
$_SESSION["phishing_feedback"] = [
    "type" => "warning",
    "message" => "This was a simulated phishing link. In a real attack, clicking could expose your password, financial information, or device.",
];

header("Location: phishing-email-view.php?id=" . $assignmentId);
exit();
