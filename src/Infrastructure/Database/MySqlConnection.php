<?php

namespace EmailCampaign\Infrastructure\Database;

use EmailCampaign\Config\Config;

class MySqlConnection
{
    private \PDO $connection;
    private static ?self $instance = null;

    private function __construct()
    {
        $config = Config::getInstance()->get('database.mysql');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $this->connection = new \PDO($dsn, $config['username'], $config['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}
