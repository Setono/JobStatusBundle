<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Starter;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Workflow\Registry;

final class Starter implements StarterInterface
{
    private Registry $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function start(Job $job = null, int $steps = null): Job
    {
        if (null === $job) {
            $job = new Job();
        }

        if (null !== $steps) {
            $job->setSteps($steps);
        }

        if (!$this->workflowRegistry->has($job, JobWorkflow::NAME)) {
            return $job;
        }

        $workflow = $this->workflowRegistry->get($job, JobWorkflow::NAME);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_START)) {
            return $job;
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_START);

        return $job;
    }
}
