<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class JobsCommand extends Command
{
    protected static $defaultName = 'setono:job-status:jobs';

    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        parent::__construct();

        $this->jobRepository = $jobRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobs = $this->jobRepository->findRunningJobs();
        $io = new SymfonyStyle($input, $output);

        $rows = [];
        foreach ($jobs as $job) {
            $startedAt = $job->getStartedAt();
            Assert::notNull($startedAt);

            $rows[] = [
                $job->getType(),
                $job->getName(),
                $startedAt->format(\DATE_ATOM),
                $job->getUpdatedAt()->format(\DATE_ATOM),
            ];
        }

        $io->table(['Type', 'Name', 'Started at', 'Last update'], $rows);

        return 0;
    }
}
