<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener;

use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\Finisher\FinisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CheckJobFinishedEventSubscriber implements EventSubscriberInterface
{
    private FinisherInterface $finisher;

    public function __construct(FinisherInterface $finisher)
    {
        $this->finisher = $finisher;
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

        $this->finisher->finish($job);
    }
}
