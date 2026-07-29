<?php

function getUserByID(PDO $pfo, int $user_id) {
    $sql = "SELECT first_name,
                   last_name,
                   email,
                   role,
                   created_at,
                   password_hash
            FROM users
            WHERE id = :id";

    $stmt = $pfo->prepare($sql);
    $stmt->execute([
        ":id" => $user_id,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTotalUsers(PDO $pfo) {
    $stmt = $pfo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function getAdminUsersCount(PDO $pfo) {
    $stmt = $pfo->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
    $stmt->execute([':role' => 'admin']);
    return (int) $stmt->fetchColumn();
}

function getRecentSignupsCount(PDO $pfo, int $days = 30) {
    $date = (new DateTime())->modify("-{$days} days")->format('Y-m-d H:i:s');
    $stmt = $pfo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= :date");
    $stmt->execute([':date' => $date]);
    return (int) $stmt->fetchColumn();
}

function getAssignedTrainingsCount(PDO $pfo) {
    $stmt = $pfo->prepare("SELECT COUNT(*) FROM training_assignments");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function getTotalBadgesCount(PDO $pfo) {
    $stmt = $pfo->prepare("SELECT COUNT(*) FROM user_badges");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function getAverageScore(PDO $pfo) {
    $stmt = $pfo->prepare("SELECT AVG(score) FROM user_attempts WHERE status = 'completed'");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    return $val !== null ? (float) $val : null;
}
function updateUserPassword(PDO $pfo, int $user_id, string $password_hash) {
    $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
    $stmt = $pfo->prepare($sql);
    $stmt->execute([
        ":password_hash" => $password_hash,
        ":id" => $user_id,
    ]);
}

function getAssignedTrainingCount(PDO $pfo, int $user_id) {
    $sql = "SELECT COUNT(*) as count
            FROM training_assignments
            WHERE user_id = :user_id";

    $stmt = $pfo->prepare($sql);
    $stmt->execute([
        ":user_id" => $user_id,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getBadgeCount(PDO $pfo, int $user_id) {
    $sql = "SELECT COUNT(*) as count
            FROM user_badges
            WHERE user_id = :id";

    $stmt = $pfo->prepare($sql);
    $stmt->execute([
        ":id" => $user_id,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getLastTrainingScore(PDO $pfo, int $user_id) {
    $sql = "SELECT score
            FROM user_attempts
            WHERE user_id = :id
            ORDER BY completed_at DESC
            LIMIT 1";

    $stmt = $pfo->prepare($sql);
    $stmt->execute([
        ":id" => $user_id,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getNextAssignedTraining(PDO $pfo, int $user_id) {
    $sql = "SELECT tm.title, ta.assigned_at
            FROM training_assignments ta
            JOIN training_modules tm ON ta.training_id = tm.id
            WHERE ta.user_id = :id AND ta.status = 'assigned'
            ORDER BY ta.assigned_at ASC
            LIMIT 1";

    $stmt = $pfo->prepare($sql);
    $stmt->execute([
        ":id" => $user_id,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}