<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Updater;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;

interface ProgressUpdaterInterface
{
    /**
     * Will update the given job by advancing the steps by $steps
     */
    public function update(JobInterface $job, int $steps = 1): void;
}
