<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Setono\JobStatusBundle\Entity\JobInterface;

interface JobRepositoryInterface extends ObjectRepository
{
    /**
     * @psalm-return list<JobInterface>
     */
    public function findRunningJobs(int $limit = 1000, int $offset = null): array;
}
