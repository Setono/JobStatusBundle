<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener;

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
            StepCompletedEvent::class => 'ensure', // todo add priority that is HIGHER than UpdateJobProgressEventSubscriber
        ];
    }

    public function ensure(StepCompletedEvent $event): void
    {
        $this->starter->start($event->getJob());
    }
}
