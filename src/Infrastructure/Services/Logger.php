<?php

namespace EmailCampaign\Infrastructure\Services;

use EmailCampaign\Domain\Interfaces\LoggerInterface;
use EmailCampaign\Config\Config;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger implements LoggerInterface
{
    private MonologLogger $logger;

    public function __construct()
    {
        $config = Config::getInstance();

        $logPath = $config->get('logging.path', 'storage/logs/campaign.log');
        $logLevel = $this->getLogLevel($config->get('logging.level', 'info'));

        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logger = new MonologLogger('email_campaign');

        $handler = new StreamHandler($logPath, $logLevel);

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            "Y-m-d H:i:s",
            true,
            true
        );

        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
    }

    private function getLogLevel(string $level): int
    {
        $levels = [
            'debug' => MonologLogger::DEBUG,
            'info' => MonologLogger::INFO,
            'notice' => MonologLogger::NOTICE,
            'warning' => MonologLogger::WARNING,
            'error' => MonologLogger::ERROR,
            'critical' => MonologLogger::CRITICAL,
            'alert' => MonologLogger::ALERT,
            'emergency' => MonologLogger::EMERGENCY,
        ];

        return $levels[$level] ?? MonologLogger::INFO;
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
}
