<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener\Workflow;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Webmozart\Assert\Assert;

/**
 * This class listens to the 'finish' transition of the
 * job workflow and sets the appropriate variables
 */
final class FinishJobEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $eventName = sprintf('workflow.%s.transition.%s', JobWorkflow::NAME, JobWorkflow::TRANSITION_FINISH);

        return [
            $eventName => 'finish',
        ];
    }

    public function finish(Event $event): void
    {
        /** @var Job|mixed $job */
        $job = $event->getSubject();
        Assert::isInstanceOf($job, Job::class);

        $job->setFinishedAt(new \DateTime());

        $steps = $job->getSteps();
        if (null !== $steps) {
            $job->setStep($steps);
        }
    }
}
