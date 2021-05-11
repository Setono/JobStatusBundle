<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Starter;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Factory\JobFactoryInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Workflow\Registry;

final class Starter implements StarterInterface
{
    use ORMManagerTrait;

    private Registry $workflowRegistry;

    private JobFactoryInterface $jobFactory;

    public function __construct(
        Registry $workflowRegistry,
        ManagerRegistry $managerRegistry,
        JobFactoryInterface $jobFactory
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->managerRegistry = $managerRegistry;
        $this->jobFactory = $jobFactory;
    }

    public function start(JobInterface $job = null, int $steps = null, bool $flush = true): JobInterface
    {
        if (null === $job) {
            $job = $this->jobFactory->createNew();
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

        if ($flush) {
            $manager = $this->getManager($job);
            $manager->persist($job);
            $manager->flush();
        }

        return $job;
    }
}
