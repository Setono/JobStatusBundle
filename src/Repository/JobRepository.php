<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Setono\JobStatusBundle\Entity\JobInterface;
use Webmozart\Assert\Assert;

class JobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    public function findRunningJobs(int $limit = 1000, int $offset = null): array
    {
        $jobs = $this->findBy(['state' => JobInterface::STATE_RUNNING], ['updatedAt' => 'DESC'], $limit, $offset);
        Assert::allIsInstanceOf($jobs, JobInterface::class);

        return $jobs;
    }
}
