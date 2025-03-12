<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class CustomLogger
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function log(string $message): void
    {
        $this->logger->info($message);
    }
}