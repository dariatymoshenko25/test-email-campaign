<?php

namespace EmailCampaign\Domain\Interfaces;

use EmailCampaign\Domain\Entities\User;

interface UserRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $offset
     * @return array<User>
     */
    public function getActiveUsers(int $limit = 100, int $offset = 0): array;

    /**
     * @return int
     */
    public function countActiveUsers(): int;
}
