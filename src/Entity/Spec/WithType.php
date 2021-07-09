<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity\Spec;

use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\BaseSpecification;

final class WithType extends BaseSpecification
{
    private string $type;

    public function __construct(string $type, ?string $context = null)
    {
        parent::__construct($context);

        $this->type = $type;
    }

    protected function getSpec()
    {
        return Spec::eq('type', $this->type);
    }
}
