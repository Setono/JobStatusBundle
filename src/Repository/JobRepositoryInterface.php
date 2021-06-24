<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Setono\JobStatusBundle\Entity\JobInterface;

interface JobRepositoryInterface extends ObjectRepository
{
    /**
     * @param array<string, string> $orderBy
     * @psalm-return list<JobInterface>
     */
    public function findRunning(array $orderBy = ['updatedAt' => 'DESC'], int $limit = 1000, int $offset = null): array;

    /**
     * @param array<string, string>|null $orderBy
     * @psalm-return list<JobInterface>
     */
    public function findByType(string $type, array $orderBy = null, int $limit = 1000, int $offset = null): array;

    /**
     * Returns true if an exclusive job of the given type is running
     */
    public function hasExclusiveRunningJob(string $type): bool;
}
