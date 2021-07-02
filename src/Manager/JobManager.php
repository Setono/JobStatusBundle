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
use Symfony\Component\Workflow\Exception\LogicException;
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

        $this->getManager($job)->persist($job);

        if (null !== $steps) {
            $job->setSteps($steps);
        }

        $this->execute($job, function (JobInterface $job) {
            $workflow = $this->getWorkflow($job);

            if (!$workflow->can($job, JobWorkflow::TRANSITION_START)) {
                throw CannotStartJobException::transitionBlocked(JobWorkflow::TRANSITION_START);
            }

            $workflow->apply($job, JobWorkflow::TRANSITION_START);
        }, $flush);

        return $job;
    }

    public function finish(JobInterface $job, bool $flush = true): void
    {
        $this->execute($job, function (JobInterface $job) {
            $workflow = $this->getWorkflow($job);

            try {
                $workflow->apply($job, JobWorkflow::TRANSITION_FINISH);
            } catch (LogicException $e) {
                $job->setError($e->getMessage());
                $workflow->apply($job, JobWorkflow::TRANSITION_FAIL);
            }
        }, $flush);
    }

    public function timeout(JobInterface $job, bool $flush = true): void
    {
        $this->execute($job, function (JobInterface $job) {
            $workflow = $this->getWorkflow($job);

            try {
                $workflow->apply($job, JobWorkflow::TRANSITION_TIMEOUT);
            } catch (LogicException $e) {
                $job->setError($e->getMessage());
                $workflow->apply($job, JobWorkflow::TRANSITION_FAIL);
            }
        }, $flush);
    }

    public function advance(JobInterface $job, int $steps = 1, bool $flush = true): void
    {
        $this->execute($job, function (JobInterface $job) use ($steps) {
            $job->advance($steps);
        }, $flush);
    }

    /**
     * Will execute the callback and try to flush. If the flush throws an optimistic lock exception
     * we will refresh the Job entity and do it all over again. This will continue as long as the
     * back off strategy allows
     *
     * @throws OptimisticLockException
     */
    private function execute(JobInterface $job, \Closure $callback, bool $flush): void
    {
        $tries = 0;
        $manager = $this->getManager($job);

        while (true) {
            $callback->call($this, $job, $manager);

            try {
                if ($flush) {
                    $manager->flush();
                }

                break;
            } catch (OptimisticLockException $e) {
                $this->backOffStrategy->backOff(++$tries, $e);

                $manager->refresh($job);
            }
        }
    }

    private function getWorkflow(JobInterface $job): WorkflowInterface
    {
        return $this->workflowRegistry->get($job, JobWorkflow::NAME);
    }
}
