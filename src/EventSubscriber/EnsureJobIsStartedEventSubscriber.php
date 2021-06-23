<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventSubscriber;

use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\Starter\StarterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EnsureJobIsStartedEventSubscriber implements EventSubscriberInterface
{
    private StarterInterface $starter;

    public function __construct(StarterInterface $starter)
    {
        $this->starter = $starter;
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

        $this->starter->start($event->getJob());
    }
}
