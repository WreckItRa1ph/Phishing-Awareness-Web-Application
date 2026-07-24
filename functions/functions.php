<?php

function getUserByID(PDO $pfo, int $user_id) {
    $sql = "SELECT first_name,
                   last_name,
                   email,
                   role,
                   created_at
            FROM users
            WHERE id = :id";

            $stmt = $pfo->prepare($sql);
            $stmt->execute([
                ":id" => $user_id,
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
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