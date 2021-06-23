<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Finisher;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Workflow\Registry;

final class Finisher implements FinisherInterface
{
    use ORMManagerTrait;

    private Registry $workflowRegistry;

    public function __construct(Registry $workflowRegistry, ManagerRegistry $managerRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->managerRegistry = $managerRegistry;
    }

    public function finish(JobInterface $job, bool $flush = true): void
    {
        $workflow = $this->workflowRegistry->get($job, JobWorkflow::NAME);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_FINISH)) {
            return;
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_FINISH);

        if ($flush) {
            $manager = $this->getManager($job);
            $manager->flush();
        }
    }
}
