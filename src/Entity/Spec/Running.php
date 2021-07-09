<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity\Spec;

use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\BaseSpecification;
use Setono\JobStatusBundle\Entity\JobInterface;

final class Running extends BaseSpecification
{
    protected function getSpec()
    {
        return Spec::eq('state', JobInterface::STATE_RUNNING);
    }
}
