<?php

function requireLoggedInUser(): void
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function getCsrfToken(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function verifyCsrfToken(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], $token);
}

function getPhishingAssignments(PDO $pdo, int $userId): array
{
    $sql = "SELECT pa.id AS assignment_id,
                   pa.status,
                   pa.assigned_at,
                   pa.opened_at,
                   pa.clicked_at,
                   pa.reported_at,
                   pa.ignored_at,
                   pt.category,
                   pt.sender_name,
                   pt.sender_email,
                   pt.subject,
                   pt.preview_text,
                   pt.difficulty
            FROM phishing_assignments pa
            INNER JOIN phishing_templates pt ON pt.id = pa.template_id
            WHERE pa.user_id = :user_id
            ORDER BY pa.assigned_at DESC, pa.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":user_id" => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPhishingAssignment(PDO $pdo, int $assignmentId, int $userId)
{
    $sql = "SELECT pa.id AS assignment_id,
                   pa.status,
                   pa.assigned_at,
                   pa.opened_at,
                   pa.clicked_at,
                   pa.reported_at,
                   pa.ignored_at,
                   pt.category,
                   pt.sender_name,
                   pt.sender_email,
                   pt.subject,
                   pt.preview_text,
                   pt.body_html,
                   pt.link_label,
                   pt.difficulty,
                   pt.red_flags
            FROM phishing_assignments pa
            INNER JOIN phishing_templates pt ON pt.id = pa.template_id
            WHERE pa.id = :assignment_id AND pa.user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":assignment_id" => $assignmentId,
        ":user_id" => $userId,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function markPhishingEmailOpened(PDO $pdo, int $assignmentId, int $userId): void
{
    $sql = "UPDATE phishing_assignments
            SET opened_at = COALESCE(opened_at, NOW()),
                status = CASE WHEN status = 'unopened' THEN 'opened' ELSE status END
            WHERE id = :assignment_id AND user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":assignment_id" => $assignmentId,
        ":user_id" => $userId,
    ]);
}

function updatePhishingAction(PDO $pdo, int $assignmentId, int $userId, string $action): bool
{
    $allowed = ["clicked", "reported", "ignored"];
    if (!in_array($action, $allowed, true)) {
        return false;
    }

    $timestampColumn = [
        "clicked" => "clicked_at",
        "reported" => "reported_at",
        "ignored" => "ignored_at",
    ][$action];

    $sql = "UPDATE phishing_assignments
            SET {$timestampColumn} = COALESCE({$timestampColumn}, NOW()),
                opened_at = COALESCE(opened_at, NOW()),
                status = :status
            WHERE id = :assignment_id AND user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":status" => $action,
        ":assignment_id" => $assignmentId,
        ":user_id" => $userId,
    ]);

    return $stmt->rowCount() > 0;
}

function getPhishingSummary(PDO $pdo, int $userId): array
{
    $sql = "SELECT COUNT(*) AS total,
                   SUM(status = 'unopened') AS unopened,
                   SUM(status = 'opened') AS opened,
                   SUM(status = 'clicked') AS clicked,
                   SUM(status = 'reported') AS reported,
                   SUM(status = 'ignored') AS ignored
            FROM phishing_assignments
            WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":user_id" => $userId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach ($summary as $key => $value) {
        $summary[$key] = (int) ($value ?? 0);
    }

    return $summary;
}

function getAdminPhishingSummary(PDO $pdo): array
{
    $sql = "SELECT COUNT(*) AS total,
                   SUM(status = 'unopened') AS unopened,
                   SUM(status = 'opened') AS opened,
                   SUM(status = 'clicked') AS clicked,
                   SUM(status = 'reported') AS reported,
                   SUM(status = 'ignored') AS ignored
            FROM phishing_assignments";

    $summary = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    foreach ($summary as $key => $value) {
        $summary[$key] = (int) ($value ?? 0);
    }
    return $summary;
}
