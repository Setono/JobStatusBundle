<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Happyr\DoctrineSpecification\Repository\EntitySpecificationRepositoryTrait;
use Setono\JobStatusBundle\Entity\JobInterface;

/**
 * @extends ServiceEntityRepository<JobInterface>
 */
class JobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    use EntitySpecificationRepositoryTrait;

    public function findRunning(array $orderBy = ['updatedAt' => 'DESC'], int $limit = 1000, int $offset = null): array
    {
        return $this->findBy(['state' => JobInterface::STATE_RUNNING], $orderBy, $limit, $offset);
    }

    public function findRunningByType(
        string $type,
        array $orderBy = ['updatedAt' => 'DESC'],
        int $limit = 1000,
        int $offset = null
    ): array {
        return $this->findBy(['type' => $type, 'state' => JobInterface::STATE_RUNNING], $orderBy, $limit, $offset);
    }

    public function findByType(string $type, array $orderBy = null, int $limit = 1000, int $offset = null): array
    {
        return $this->findBy(['type' => $type], $orderBy, $limit, $offset);
    }

    public function findCandidatesForTimeout(array $orderBy = null, int $limit = 1000, int $offset = null): array
    {
        /** @psalm-var list<JobInterface> $res */
        $res = $this->createQueryBuilder('o')
            ->andWhere('DATE_ADD(o.updatedAt, INTERVAL o.waitForTimeout SECOND) < :now')
            ->andWhere('o.state = :state')
            ->setParameter('state', JobInterface::STATE_RUNNING)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        return $res;
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
