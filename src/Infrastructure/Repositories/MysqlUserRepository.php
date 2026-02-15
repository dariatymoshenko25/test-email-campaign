<?php

namespace EmailCampaign\Infrastructure\Repositories;

use EmailCampaign\Domain\Entities\User;
use EmailCampaign\Domain\Interfaces\UserRepositoryInterface;
use EmailCampaign\Infrastructure\Database\MySqlConnection;

class MysqlUserRepository implements UserRepositoryInterface
{
    private \PDO $connection;

    public function __construct(?MySqlConnection $connection = null)
    {
        $this->connection = ($connection ?? MySqlConnection::getInstance())->getConnection();
    }

    public function getActiveUsers(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->connection->prepare("
            SELECT id, email, is_active 
            FROM users 
            WHERE is_active = 1 
            ORDER BY id 
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        foreach ($stmt->fetchAll() as $row) {
            $users[] = new User(
                (int)$row['id'],
                $row['email'],
                (bool)$row['is_active']
            );
        }

        return $users;
    }

    public function countActiveUsers(): int
    {
        $stmt = $this->connection->query("
            SELECT COUNT(*) 
            FROM users 
            WHERE is_active = 1
        ");

        return (int)$stmt->fetchColumn();
    }
}
