<?php

namespace EmailCampaign\Config;

use Dotenv\Dotenv;

class Config
{
    private static ?self $instance = null;
    private array $config = [];

    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfiguration();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    private function loadConfiguration(): void
    {
        $this->config['database']['mysql'] = [
            'host' => $_ENV['DB_MYSQL_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_MYSQL_PORT'] ?? 3306,
            'database' => $_ENV['DB_MYSQL_DATABASE'],
            'username' => $_ENV['DB_MYSQL_USERNAME'],
            'password' => $_ENV['DB_MYSQL_PASSWORD'],
            'charset' => $_ENV['DB_MYSQL_CHARSET'] ?? 'utf8mb4',
        ];

        $this->config['database']['mongo'] = [
            'uri' => $_ENV['DB_MONGO_URI'],
            'database' => $_ENV['DB_MONGO_DATABASE'],
        ];

        $this->config['mail'] = [
            'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
            'host' => $_ENV['MAIL_HOST'],
            'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'username' => $_ENV['MAIL_USERNAME'],
            'password' => $_ENV['MAIL_PASSWORD'],
            'from' => [
                'address' => $_ENV['MAIL_FROM_ADDRESS'],
                'name' => $_ENV['MAIL_FROM_NAME'],
            ],
        ];

        $this->config['campaign'] = [
            'batch_size' => (int)($_ENV['CAMPAIGN_BATCH_SIZE'] ?? 100),
            'email_subject' => $_ENV['CAMPAIGN_EMAIL_SUBJECT'],
            'email_template' => $_ENV['CAMPAIGN_EMAIL_TEMPLATE'],
            'min_product_views' => (int)($_ENV['CAMPAIGN_MIN_PRODUCT_VIEWS'] ?? 3),
        ];

        $this->config['logging'] = [
            'channel' => $_ENV['LOG_CHANNEL'] ?? 'file',
            'path' => $_ENV['LOG_PATH'] ?? 'storage/logs/campaign.log',
            'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        ];

        $this->config['app'] = [
            'env' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
        ];
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}