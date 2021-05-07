<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Finisher;

use Setono\JobStatusBundle\Entity\Job;

interface FinisherInterface
{
    /**
     * Finishes the given job
     */
    public function finish(Job $job): void;
}
