<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Manager;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use EventSauce\BackOff\BackOffStrategy;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Exception\CannotStartJobException;
use Setono\JobStatusBundle\Factory\JobFactoryInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

final class JobManager implements JobManagerInterface
{
    use ORMManagerTrait;

    private Registry $workflowRegistry;

    private JobRepositoryInterface $jobRepository;

    private JobFactoryInterface $jobFactory;

    private BackOffStrategy $backOffStrategy;

    public function __construct(
        Registry $workflowRegistry,
        ManagerRegistry $managerRegistry,
        JobRepositoryInterface $jobRepository,
        JobFactoryInterface $jobFactory,
        BackOffStrategy $backOffStrategy
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->managerRegistry = $managerRegistry;
        $this->jobRepository = $jobRepository;
        $this->jobFactory = $jobFactory;
        $this->backOffStrategy = $backOffStrategy;
    }

    public function start(JobInterface $job = null, int $steps = null, bool $flush = true): JobInterface
    {
        if (null === $job) {
            $job = $this->jobFactory->createNew();
        }

        if ($this->jobRepository->hasExclusiveRunningJob($job->getType())) {
            throw CannotStartJobException::exclusiveJobRunning($job->getType());
        }

        if (null !== $steps) {
            $job->setSteps($steps);
        }

        $workflow = $this->getWorkflow($job);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_START)) {
            throw CannotStartJobException::transitionBlocked(JobWorkflow::TRANSITION_START);
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_START);

        if ($flush) {
            $manager = $this->getManager($job);
            $manager->persist($job);
            $manager->flush();
        }

        return $job;
    }

    public function finish(JobInterface $job, bool $flush = true): void
    {
        $workflow = $this->getWorkflow($job);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_FINISH)) {
            // todo should throw exception?
            return;
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_FINISH);

        if ($flush) {
            $manager = $this->getManager($job);
            $manager->flush();
        }
    }

    public function timeout(JobInterface $job, bool $flush = true): void
    {
        $workflow = $this->getWorkflow($job);

        if (!$workflow->can($job, JobWorkflow::TRANSITION_TIMEOUT)) {
            // todo should throw exception?
            return;
        }

        $workflow->apply($job, JobWorkflow::TRANSITION_TIMEOUT);

        if ($flush) {
            $manager = $this->getManager($job);
            $manager->flush();
        }
    }

    public function advance(JobInterface $job, int $steps = 1): void
    {
        $tries = 0;

        do {
            // this could look like a bug, but the $job is refreshed (in the object manager)
            // in the case self::flush returns false
            $job->advance($steps);
        } while (!$this->tryFlush($job, ++$tries));
    }

    /**
     * Returns true if the manager flushes else it 'backs off' using
     * the given back off strategy and then refreshes the job
     *
     * @throws OptimisticLockException
     */
    private function tryFlush(JobInterface $job, int $tries): bool
    {
        $manager = $this->getManager($job);

        try {
            $manager->flush();
        } catch (OptimisticLockException $e) {
            $this->backOffStrategy->backOff($tries, $e);

            $manager->refresh($job);

            return false;
        }

        return true;
    }

    private function getWorkflow(JobInterface $job): WorkflowInterface
    {
        return $this->workflowRegistry->get($job, JobWorkflow::NAME);
    }
}
