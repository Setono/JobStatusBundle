<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

final class TimeoutCommand extends Command
{
    use ORMManagerTrait;

    protected static $defaultName = 'setono:job-status:timeout';

    protected static $defaultDescription = "Clean up timed out jobs by moving them to the 'timed_out' state";

    private JobRepositoryInterface $jobRepository;

    private Registry $workflowRegistry;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        Registry $workflowRegistry,
        ManagerRegistry $managerRegistry
    ) {
        parent::__construct();

        $this->jobRepository = $jobRepository;
        $this->workflowRegistry = $workflowRegistry;
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure(): void
    {
        $this->setDescription((string) self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        do {
            $jobs = $this->jobRepository->findPassedTimeout();

            $manager = null;

            foreach ($jobs as $job) {
                $workflow = $this->getWorkflow($job);
                $workflow->apply($job, JobWorkflow::TRANSITION_TIMEOUT);

                $manager = $this->getManager($job);
                $manager->flush();
            }

            if (null !== $manager) {
                $manager->clear();
            }
        } while (count($jobs) > 0);

        return 0;
    }

    private function getWorkflow(JobInterface $job): WorkflowInterface
    {
        return $this->workflowRegistry->get($job, JobWorkflow::NAME);
    }
}
