<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Setono\JobStatusBundle\Entity\JobInterface;

/**
 * @extends ServiceEntityRepository<JobInterface>
 */
class JobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    public function findRunning(array $orderBy = ['updatedAt' => 'DESC'], int $limit = 1000, int $offset = null): array
    {
        return $this->findBy(['state' => JobInterface::STATE_RUNNING], $orderBy, $limit, $offset);
    }

    public function findRunningByType(string $type, array $orderBy = ['updatedAt' => 'DESC'], int $limit = 1000, int $offset = null): array
    {
        return $this->findBy(['type' => $type, 'state' => JobInterface::STATE_RUNNING], $orderBy, $limit, $offset);
    }

    public function findByType(string $type, array $orderBy = null, int $limit = 1000, int $offset = null): array
    {
        return $this->findBy(['type' => $type], $orderBy, $limit, $offset);
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
