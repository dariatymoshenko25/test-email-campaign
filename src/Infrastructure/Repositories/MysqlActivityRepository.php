<?php

namespace EmailCampaign\Infrastructure\Repositories;

use EmailCampaign\Domain\Entities\Activity;
use EmailCampaign\Domain\Interfaces\ActivityRepositoryInterface;
use EmailCampaign\Infrastructure\Database\MySqlConnection;

class MysqlActivityRepository implements ActivityRepositoryInterface
{
    private \PDO $connection;

    public function __construct(?MySqlConnection $connection = null)
    {
        $this->connection = ($connection ?? MySqlConnection::getInstance())->getConnection();
    }

    public function getUserActivityLast24h(int $userId): array
    {
        $stmt = $this->connection->prepare("
            SELECT user_id, activity_type, created_at 
            FROM user_activities 
            WHERE user_id = :user_id 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY created_at DESC
        ");

        $stmt->execute(['user_id' => $userId]);

        $activities = [];
        foreach ($stmt->fetchAll() as $row) {
            $activities[] = new Activity(
                (int)$row['user_id'],
                $row['activity_type'],
                new \DateTime($row['created_at'])
            );
        }

        return $activities;
    }

    public function hasActivityLast24h(int $userId): bool
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) 
            FROM user_activities 
            WHERE user_id = :user_id 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");

        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function countActivityLast24h(int $userId): int
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) 
            FROM user_activities 
            WHERE user_id = :user_id 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");

        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}
