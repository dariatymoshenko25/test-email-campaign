<?php

namespace EmailCampaign\Domain\Entities;

class Activity
{
    private int $userId;
    private string $type;
    private \DateTime $createdAt;

    public function __construct(int $userId, string $type, \DateTime $createdAt)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->createdAt = $createdAt;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
