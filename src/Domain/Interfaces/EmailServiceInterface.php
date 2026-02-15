<?php

namespace EmailCampaign\Domain\Interfaces;

interface EmailServiceInterface
{
    /**
     * @param string $email
     * @param string $subject
     * @param string $template
     * @param array $data
     * @return bool
     */
    public function send(string $email, string $subject, string $template, array $data = []): bool;
}