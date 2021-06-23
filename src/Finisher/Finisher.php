<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Finisher;

use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Workflow\Registry;

final class Finisher implements FinisherInterface
{
    private Registry $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function finish(JobInterface $job): void
    {
        $workflow = $this->workflowRegistry->get($job, JobWorkflow::NAME);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_FINISH)) {
            return;
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_FINISH);
    }
}
