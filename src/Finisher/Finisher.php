<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Finisher;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Workflow\Registry;

final class Finisher implements FinisherInterface
{
    private Registry $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function finish(Job $job): void
    {
        if (!$this->workflowRegistry->has($job, JobWorkflow::NAME)) {
            return;
        }

        $workflow = $this->workflowRegistry->get($job, JobWorkflow::NAME);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_FINISH)) {
            return;
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_FINISH);
    }
}
