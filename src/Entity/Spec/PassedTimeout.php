<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity\Spec;

use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\BaseSpecification;
use Setono\JobStatusBundle\Entity\JobInterface;

final class PassedTimeout extends BaseSpecification
{
    protected function getSpec()
    {
        return Spec::andX(
            Spec::lt('timesOutAt', new \DateTime()),
            Spec::eq('state', JobInterface::STATE_RUNNING)
        );
    }
}
