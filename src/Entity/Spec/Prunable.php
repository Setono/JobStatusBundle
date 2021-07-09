<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity\Spec;

use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\BaseSpecification;

final class Prunable extends BaseSpecification
{
    private \DateTimeInterface $threshold;

    /**
     * @param \DateTimeInterface $threshold doesn't return jobs updated later than this date
     */
    public function __construct(\DateTimeInterface $threshold, ?string $context = null)
    {
        parent::__construct($context);

        $this->threshold = $threshold;
    }

    protected function getSpec()
    {
        return Spec::andX(
            new NotRunning(),
            new NotUpdatedSince($this->threshold)
        );
    }
}
