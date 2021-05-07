<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Event;

use Setono\JobStatusBundle\Entity\Job;

/**
 * Fire this event using the event dispatcher every time you completed one or more steps on a given job
 */
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
