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

    public function hasExclusiveRunningJob(string $type): bool
    {
        $res = (int) $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->andWhere('o.exclusive = true')
            ->andWhere('o.state = :state')
            ->andWhere('o.type = :type')
            ->setParameter('state', JobInterface::STATE_RUNNING)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();

        return $res > 0;
    }
}
