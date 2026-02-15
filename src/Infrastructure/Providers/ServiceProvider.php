<?php

namespace EmailCampaign\Infrastructure\Providers;

use EmailCampaign\Domain\Interfaces\UserRepositoryInterface;
use EmailCampaign\Domain\Interfaces\ActivityRepositoryInterface;
use EmailCampaign\Domain\Interfaces\EmailServiceInterface;
use EmailCampaign\Domain\Interfaces\LoggerInterface;
use EmailCampaign\Domain\Services\ActivityAnalyzer;
use EmailCampaign\Domain\Services\EmailCampaignService;
use EmailCampaign\Application\CampaignRunner;
use EmailCampaign\Infrastructure\Repositories\MysqlUserRepository;
use EmailCampaign\Infrastructure\Repositories\MysqlActivityRepository;
use EmailCampaign\Infrastructure\Repositories\MongoActivityRepository;
use EmailCampaign\Infrastructure\Services\EmailService;
use EmailCampaign\Infrastructure\Services\Logger;

class ServiceProvider
{
    private static ?self $instance = null;
    private array $services = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getLogger(): LoggerInterface
    {
        if (!isset($this->services['logger'])) {
            $this->services['logger'] = new Logger();
        }

        return $this->services['logger'];
    }

    public function getUserRepository(): UserRepositoryInterface
    {
        if (!isset($this->services['user_repository'])) {
            $this->services['user_repository'] = new MysqlUserRepository();
        }

        return $this->services['user_repository'];
    }

    public function getMysqlActivityRepository(): ActivityRepositoryInterface
    {
        if (!isset($this->services['mysql_activity_repository'])) {
            $this->services['mysql_activity_repository'] = new MysqlActivityRepository();
        }

        return $this->services['mysql_activity_repository'];
    }

    public function getMongoActivityRepository(): ActivityRepositoryInterface
    {
        if (!isset($this->services['mongo_activity_repository'])) {
            $this->services['mongo_activity_repository'] = new MongoActivityRepository();
        }

        return $this->services['mongo_activity_repository'];
    }

    public function getEmailService(): EmailServiceInterface
    {
        if (!isset($this->services['email_service'])) {
            $this->services['email_service'] = new EmailService();
        }

        return $this->services['email_service'];
    }

    public function getActivityAnalyzer(): ActivityAnalyzer
    {
        if (!isset($this->services['activity_analyzer'])) {
            $this->services['activity_analyzer'] = new ActivityAnalyzer(
                $this->getMongoActivityRepository(),
                $this->getMysqlActivityRepository(),
                $this->getLogger()
            );
        }

        return $this->services['activity_analyzer'];
    }

    public function getEmailCampaignService(): EmailCampaignService
    {
        if (!isset($this->services['email_campaign_service'])) {
            $this->services['email_campaign_service'] = new EmailCampaignService(
                $this->getEmailService(),
                $this->getLogger()
            );
        }

        return $this->services['email_campaign_service'];
    }

    public function getCampaignRunner(): CampaignRunner
    {
        if (!isset($this->services['campaign_runner'])) {
            $this->services['campaign_runner'] = new CampaignRunner(
                $this->getUserRepository(),
                $this->getActivityAnalyzer(),
                $this->getEmailCampaignService(),
                $this->getLogger()
            );
        }

        return $this->services['campaign_runner'];
    }
}