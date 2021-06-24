<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventSubscriber;

use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CheckJobFinishedEventSubscriber implements EventSubscriberInterface
{
    private JobManagerInterface $jobManager;

    public function __construct(JobManagerInterface $jobManager)
    {
        $this->jobManager = $jobManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StepCompletedEvent::class => ['update', -10],
        ];
    }

    public function update(StepCompletedEvent $event): void
    {
        $job = $event->getJob();
        $steps = $job->getSteps();

        // we can't know if a job is finished if $steps is null. The user has to finish the job manually
        if (null === $steps) {
            return;
        }

        // not finished yet
        if ($steps !== $job->getStep()) {
            return;
        }

        $this->jobManager->finish($job);
    }
}
