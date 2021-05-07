<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener\Workflow;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Webmozart\Assert\Assert;

/**
 * This class listens to the 'start' transition of the job workflow and
 * resets the Job's variables together with setting a startedAt timestamp
 */
final class StartJobEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $eventName = sprintf('workflow.%s.transition.%s', JobWorkflow::NAME, JobWorkflow::TRANSITION_START);

        return [
            $eventName => 'start',
        ];
    }

    public function start(Event $event): void
    {
        /** @var Job|mixed $job */
        $job = $event->getSubject();
        Assert::isInstanceOf($job, Job::class);

        $job->setStep(0);
        $job->setStartedAt(new \DateTime());
    }
}
