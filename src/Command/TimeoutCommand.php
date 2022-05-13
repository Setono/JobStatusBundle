<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Happyr\DoctrineSpecification\Spec;
use Setono\JobStatusBundle\Entity\Spec\PassedTimeout;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TimeoutCommand extends Command
{
    protected static $defaultName = 'setono:job-status:timeout';

    protected static $defaultDescription = "Clean up timed out jobs by moving them to the 'timed_out' state";

    private JobRepositoryInterface $jobRepository;

    private JobManagerInterface $jobManager;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobManagerInterface $jobManager
    ) {
        parent::__construct();

        $this->jobRepository = $jobRepository;
        $this->jobManager = $jobManager;
    }

    protected function configure(): void
    {
        $this->setDescription((string) self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $i = 0;

        $jobs = $this->jobRepository->match(Spec::andX(
            new PassedTimeout(),
            Spec::limit(100)
        ));

        foreach ($jobs as $job) {
            $this->jobManager->timeout($job);

            ++$i;
        }

        $io->success(sprintf("%d job%s was transitioned to the 'timed_out' state", $i, $i === 1 ? '' : 's'));

        return 0;
    }
}
