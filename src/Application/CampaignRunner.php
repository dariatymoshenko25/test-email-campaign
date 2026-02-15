<?php

namespace EmailCampaign\Application;

use EmailCampaign\Domain\Interfaces\UserRepositoryInterface;
use EmailCampaign\Domain\Services\ActivityAnalyzer;
use EmailCampaign\Domain\Services\EmailCampaignService;
use EmailCampaign\Domain\Interfaces\LoggerInterface;
use EmailCampaign\Config\Config;

class CampaignRunner
{
    private UserRepositoryInterface $userRepository;
    private ActivityAnalyzer $activityAnalyzer;
    private EmailCampaignService $emailService;
    private LoggerInterface $logger;
    private int $batchSize;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ActivityAnalyzer $activityAnalyzer,
        EmailCampaignService $emailService,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->activityAnalyzer = $activityAnalyzer;
        $this->emailService = $emailService;
        $this->logger = $logger;

        $config = Config::getInstance();
        $this->batchSize = $config->get('campaign.batch_size', 100);
    }

    /**
     * @return array
     */
    public function run(): array
    {
        $stats = [
            'total_processed' => 0,
            'emails_sent' => 0,
            'errors' => 0,
            'skipped' => 0,
            'execution_time' => 0
        ];

        try {
            $totalUsers = $this->userRepository->countActiveUsers();

            if ($totalUsers === 0) {
                $this->logger->warning("No active users found");
                return $stats;
            }

            $offset = 0;
            $batchNumber = 1;

            while ($offset < $totalUsers) {
                $users = $this->userRepository->getActiveUsers($this->batchSize, $offset);

                if (empty($users)) {
                    break;
                }

                $this->logger->info("Processing batch", [
                    'batch_number' => $batchNumber,
                    'offset' => $offset,
                    'users_in_batch' => count($users)
                ]);

                foreach ($users as $user) {
                    $stats['total_processed']++;

                    try {
                        if ($this->activityAnalyzer->isUserActive($user->getId())) {
                            if ($this->emailService->sendCampaignEmail($user)) {
                                $stats['emails_sent']++;
                            } else {
                                $stats['errors']++;
                            }
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        $this->logger->error("Error processing user", [
                            'user_id' => $user->getId(),
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $offset += $this->batchSize;
                $batchNumber++;
            }

        } catch (\Exception $e) {
            $this->logger->error("Campaign failed", ['error' => $e->getMessage()]);
            throw $e;
        }

        return $stats;
    }
}