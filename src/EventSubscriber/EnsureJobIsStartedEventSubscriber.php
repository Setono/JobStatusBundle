<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventSubscriber;

use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EnsureJobIsStartedEventSubscriber implements EventSubscriberInterface
{
    private JobManagerInterface $jobManager;

    public function __construct(JobManagerInterface $jobManager)
    {
        $this->jobManager = $jobManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StepCompletedEvent::class => ['ensure', 10],
        ];
    }

    public function ensure(StepCompletedEvent $event): void
    {
        $job = $event->getJob();
        if ($job->isRunning()) {
            return;
        }

        $this->jobManager->start($event->getJob());
    }
}
