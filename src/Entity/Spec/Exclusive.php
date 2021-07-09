<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity\Spec;

use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\BaseSpecification;

final class Exclusive extends BaseSpecification
{
    protected function getSpec()
    {
        return Spec::eq('exclusive', true);
    }
}
