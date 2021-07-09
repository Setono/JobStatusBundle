<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity\Spec;

use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\BaseSpecification;

final class NotUpdatedSince extends BaseSpecification
{
    private \DateTimeInterface $since;

    public function __construct(\DateTimeInterface $since, ?string $context = null)
    {
        parent::__construct($context);

        $this->since = $since;
    }

    protected function getSpec()
    {
        return Spec::lte('updatedAt', $this->since);
    }
}
