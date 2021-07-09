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
