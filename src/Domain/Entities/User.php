<?php

namespace EmailCampaign\Domain\Entities;

class User
{
    private int $id;
    private string $email;
    private bool $isActive;

    public function __construct(int $id, string $email, bool $isActive = true)
    {
        $this->id = $id;
        $this->email = $email;
        $this->isActive = $isActive;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
