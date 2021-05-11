<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Finisher;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;

interface FinisherInterface
{
    /**
     * Finishes the given job
     */
    public function finish(JobInterface $job): void;
}
