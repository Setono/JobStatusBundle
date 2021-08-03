<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Workflow;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class JobWorkflow
{
    public const NAME = 'setono_job_status__job';

    public const TRANSITION_START = 'start';

    public const TRANSITION_FAIL = 'fail';

    public const TRANSITION_TIMEOUT = 'timeout';

    public const TRANSITION_FINISH = 'finish';

    private function __construct()
    {
    }

    /**
     * @return array<array-key, string>
     */
    public static function getStates(): array
    {
        return Job::getStates();
    }

    public static function getConfig(): array
    {
        $transitions = [];
        foreach (self::getTransitions() as $transition) {
            $transitions[$transition->getName()] = [
                'from' => $transition->getFroms(),
                'to' => $transition->getTos(),
            ];
        }

        return [
            self::NAME => [
                'type' => 'state_machine',
                'marking_store' => [
                    'type' => 'method',
                    'property' => 'state',
                ],
                'supports' => Job::class,
                'initial_marking' => JobInterface::STATE_PENDING,
                'places' => self::getStates(),
                'transitions' => $transitions,
            ],
        ];
    }

    public static function getWorkflow(EventDispatcherInterface $eventDispatcher): Workflow
    {
        $definitionBuilder = new DefinitionBuilder(self::getStates(), self::getTransitions());

        return new Workflow(
            $definitionBuilder->build(),
            new MethodMarkingStore(true, 'state'),
            $eventDispatcher,
            self::NAME
        );
    }

    /**
     * @return array<array-key, Transition>
     */
    public static function getTransitions(): array
    {
        return [
            new Transition(self::TRANSITION_START, JobInterface::STATE_PENDING, JobInterface::STATE_RUNNING),
            new Transition(self::TRANSITION_FAIL, JobInterface::STATE_RUNNING, JobInterface::STATE_FAILED),
            new Transition(self::TRANSITION_TIMEOUT, JobInterface::STATE_RUNNING, JobInterface::STATE_TIMED_OUT),
            new Transition(self::TRANSITION_FINISH, JobInterface::STATE_RUNNING, JobInterface::STATE_FINISHED),
        ];
    }
}
