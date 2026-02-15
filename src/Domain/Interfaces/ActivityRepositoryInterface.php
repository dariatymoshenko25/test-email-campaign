<?php

namespace EmailCampaign\Domain\Interfaces;

use EmailCampaign\Domain\Entities\Activity;

interface ActivityRepositoryInterface
{
    /**
     * @param int $userId
     * @return array<Activity>
     */
    public function getUserActivityLast24h(int $userId): array;

    /**
     * @param int $userId
     * @return bool
     */
    public function hasActivityLast24h(int $userId): bool;

    /**
     * @param int $userId
     * @return int
     */
    public function countActivityLast24h(int $userId): int;
}
