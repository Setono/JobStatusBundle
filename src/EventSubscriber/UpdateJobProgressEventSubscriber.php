<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventSubscriber;

use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\Updater\ProgressUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateJobProgressEventSubscriber implements EventSubscriberInterface
{
    private ProgressUpdaterInterface $progressUpdater;

    public function __construct(ProgressUpdaterInterface $progressUpdater)
    {
        $this->progressUpdater = $progressUpdater;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StepCompletedEvent::class => 'update',
        ];
    }

    public function update(StepCompletedEvent $event): void
    {
        $this->progressUpdater->update($event->getJob(), $event->getSteps());
    }
}
