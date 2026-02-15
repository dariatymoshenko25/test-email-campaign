<?php

namespace EmailCampaign\Domain\Services;

use EmailCampaign\Domain\Entities\User;
use EmailCampaign\Domain\Interfaces\EmailServiceInterface;
use EmailCampaign\Domain\Interfaces\LoggerInterface;
use EmailCampaign\Config\Config;

class EmailCampaignService
{
    private EmailServiceInterface $emailService;
    private LoggerInterface $logger;
    private string $emailSubject;
    private string $emailTemplate;

    public function __construct(
        EmailServiceInterface $emailService,
        LoggerInterface $logger
    ) {
        $config = Config::getInstance();

        $this->emailService = $emailService;
        $this->logger = $logger;
        $this->emailSubject = $config->get('campaign.email_subject');
        $this->emailTemplate = $config->get('campaign.email_template');
    }

    /**
     * @param User $user
     * @return bool
     */
    public function sendCampaignEmail(User $user): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        try {
            $sent = $this->emailService->send(
                $user->getEmail(),
                $this->emailSubject,
                $this->emailTemplate,
                [
                    'user' => $user,
                    'current_date' => date('d.m.Y')
                ]
            );

            if ($sent) {
                $this->logger->info("Email sent successfully", [
                    'user_id' => $user->getId(),
                    'email' => $user->getEmail()
                ]);
            } else {
                $this->logger->warning("Email sending failed", [
                    'user_id' => $user->getId(),
                    'email' => $user->getEmail()
                ]);
            }

            return $sent;
        } catch (\Exception $e) {
            $this->logger->error("Error sending email", [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}