<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Event;

use Setono\JobStatusBundle\Entity\JobInterface;

/**
 * Fire this event using the event dispatcher every time you completed one or more steps on a given job
 */
final class StepCompletedEvent
{
    private JobInterface $job;

    private int $steps;

    public function __construct(JobInterface $job, int $steps = 1)
    {
        $this->job = $job;
        $this->steps = $steps;
    }

    public function getJob(): JobInterface
    {
        return $this->job;
    }

    public function getSteps(): int
    {
        return $this->steps;
    }
}
