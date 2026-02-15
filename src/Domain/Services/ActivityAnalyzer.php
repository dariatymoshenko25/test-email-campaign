<?php

namespace EmailCampaign\Domain\Services;

use EmailCampaign\Domain\Interfaces\ActivityRepositoryInterface;
use EmailCampaign\Domain\Interfaces\LoggerInterface;

class ActivityAnalyzer
{
    private ActivityRepositoryInterface $mongoRepository;
    private ActivityRepositoryInterface $mysqlRepository;
    private LoggerInterface $logger;

    public function __construct(
        ActivityRepositoryInterface $mongoRepository,
        ActivityRepositoryInterface $mysqlRepository,
        LoggerInterface $logger
    ) {
        $this->mongoRepository = $mongoRepository;
        $this->mysqlRepository = $mysqlRepository;
        $this->logger = $logger;
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isUserActive(int $userId): bool
    {
        try {
            if ($this->mongoRepository->hasActivityLast24h($userId)) {
                return true;
            }

            if ($this->mysqlRepository->hasActivityLast24h($userId)) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error("Error checking user activity", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
