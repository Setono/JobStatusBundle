<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Event;

use Setono\JobStatusBundle\Entity\Job;

final class StepCompletedEvent
{
    private Job $job;

    private int $steps;

    public function __construct(Job $job, int $steps = 1)
    {
        $this->job = $job;
        $this->steps = $steps;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function getSteps(): int
    {
        return $this->steps;
    }
}
