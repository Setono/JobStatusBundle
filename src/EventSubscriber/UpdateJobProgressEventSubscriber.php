<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventSubscriber;

use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateJobProgressEventSubscriber implements EventSubscriberInterface
{
    private JobManagerInterface $jobManager;

    public function __construct(JobManagerInterface $jobManager)
    {
        $this->jobManager = $jobManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StepCompletedEvent::class => 'update',
        ];
    }

    public function update(StepCompletedEvent $event): void
    {
        $this->jobManager->advance($event->getJob(), $event->getSteps());
    }
}
