<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TimeoutCommand extends Command
{
    use ORMManagerTrait;

    protected static $defaultName = 'setono:job-status:timeout';

    protected static $defaultDescription = "Clean up timed out jobs by moving them to the 'timed_out' state";

    private JobRepositoryInterface $jobRepository;

    private JobManagerInterface $jobManager;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobManagerInterface $jobManager,
        ManagerRegistry $managerRegistry
    ) {
        parent::__construct();

        $this->jobRepository = $jobRepository;
        $this->jobManager = $jobManager;
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
                // notice that this call can also produce an exception, but if we catch it we might end up in an
                // infinite loop because the state can then still be 'running' and obviously the timeout will still
                // be passed
                $this->jobManager->timeout($job);

                $manager = $this->getManager($job);
            }

            if (null !== $manager) {
                $manager->clear();
            }
        } while (count($jobs) > 0);

        return 0;
    }
}
