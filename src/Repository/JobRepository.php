<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Repository\EntitySpecificationRepositoryTrait;
use Setono\JobStatusBundle\Entity\JobInterface;
use Webmozart\Assert\Assert;

/**
 * @extends ServiceEntityRepository<JobInterface>
 */
class JobRepository extends ServiceEntityRepository implements JobRepositoryInterface
{
    use EntitySpecificationRepositoryTrait;

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

    public function findLastJobByType(string $type): ?JobInterface
    {
        $res = $this->createQueryBuilder('o')
            ->andWhere('o.type = :type')
            ->addOrderBy('o.startedAt', 'DESC')
            ->setParameter('type', $type)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        Assert::nullOrIsInstanceOf($res, JobInterface::class);

        return $res;
    }

    public function findNotUpdatedSince(
        \DateTimeInterface $threshold,
        array $orderBy = null,
        int $limit = 1000,
        int $offset = null
    ): array {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.updatedAt <= :threshold')
            ->andWhere('o.state != :state')
            ->setParameter('threshold', $threshold)
            ->setParameter('state', JobInterface::STATE_RUNNING)
        ;

        self::applyOrderBy($qb, $orderBy);
        self::applyPagination($qb, $limit, $offset);

        /** @psalm-var list<JobInterface> $res */
        $res = $qb->getQuery()->getResult();

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

    /**
     * @param array<string, string>|null $orderBy
     */
    private static function applyOrderBy(QueryBuilder $qb, ?array $orderBy): void
    {
        if (null === $orderBy) {
            return;
        }

        foreach ($orderBy as $field => $order) {
            $qb->addOrderBy($field, $order);
        }
    }

    private static function applyPagination(QueryBuilder $qb, int $limit, ?int $offset): void
    {
        $qb->setMaxResults($limit);

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }
    }
}
