<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Happyr\DoctrineSpecification\Repository\EntitySpecificationRepositoryInterface;
use Setono\JobStatusBundle\Entity\JobInterface;

interface JobRepositoryInterface extends ObjectRepository, EntitySpecificationRepositoryInterface
{
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
     * Returns the last job that was started by the given type
     */
    public function findLastJobByType(string $type): ?JobInterface;

    /**
     * Returns a list of Jobs that wasn't updated since the given threshold
     *
     * @param array<string, string>|null $orderBy
     * @psalm-return list<JobInterface>
     */
    public function findNotUpdatedSince(\DateTimeInterface $threshold, array $orderBy = null, int $limit = 1000, int $offset = null): array;

    /**
     * Returns true if an exclusive job of the given type is running
     */
    public function hasExclusiveRunningJob(string $type): bool;
}
