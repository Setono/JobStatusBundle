<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Setono\JobStatusBundle\Entity\Job;

interface JobRepositoryInterface extends ObjectRepository
{
    /**
     * @psalm-return list<Job>
     */
    public function findRunningJobs(int $limit = 1000, int $offset = null): array;
}
