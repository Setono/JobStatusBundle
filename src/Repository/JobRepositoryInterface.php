<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Happyr\DoctrineSpecification\Repository\EntitySpecificationRepositoryInterface;

interface JobRepositoryInterface extends ObjectRepository, EntitySpecificationRepositoryInterface
{
    /**
     * Returns true if an exclusive job of the given type is running
     */
    public function hasExclusiveRunningJob(string $type): bool;
}
