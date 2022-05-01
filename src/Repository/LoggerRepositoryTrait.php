<?php declare(strict_types=1);

namespace App\Repository;

use Psr\Log\LoggerInterface;

trait LoggerRepositoryTrait
{
    protected LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
