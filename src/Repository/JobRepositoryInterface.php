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
     * @param array<string, string> $orderBy
     * @psalm-return list<JobInterface>
     */
    public function findRunningByType(string $type, array $orderBy = ['updatedAt' => 'DESC'], int $limit = 1000, int $offset = null): array;

    /**
     * @param array<string, string>|null $orderBy
     * @psalm-return list<JobInterface>
     */
    public function findByType(string $type, array $orderBy = null, int $limit = 1000, int $offset = null): array;

    /**
     * Returns a list of Jobs that are candidates for issuing a timeout transition, i.e. jobs
     * that has been inactive for a period longer than the 'wait for timeout' property
     *
     * @param array<string, string>|null $orderBy
     * @psalm-return list<JobInterface>
     */
    public function findCandidatesForTimeout(array $orderBy = null, int $limit = 1000, int $offset = null): array;

    public function findLastJobByType(string $type): ?JobInterface;

    /**
     * Returns true if an exclusive job of the given type is running
     */
    public function hasExclusiveRunningJob(string $type): bool;
}
