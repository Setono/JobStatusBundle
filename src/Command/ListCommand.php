<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\Running;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class ListCommand extends Command
{
    protected static $defaultName = 'setono:job-status:list';

    protected static $defaultDescription = 'Lists the currently running jobs';

    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        parent::__construct();

        $this->jobRepository = $jobRepository;
    }

    protected function configure(): void
    {
        $this->setDescription((string) self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<array-key, JobInterface> $jobs */
        $jobs = $this->jobRepository->match(new Running());
        $jobCount = count($jobs);
        $io = new SymfonyStyle($input, $output);

        $io->section(sprintf('%d running job%s', $jobCount, $jobCount === 1 ? '' : 's'));

        $rows = [];
        foreach ($jobs as $job) {
            $startedAt = $job->getStartedAt();
            Assert::notNull($startedAt);

            $steps = $job->getSteps();

            $rows[] = [
                $job->getName(),
                $job->getType(),
                $steps === null ? $job->getStep() : sprintf('%d / %d (%d%%)', $job->getStep(), $steps, (int) $job->getProgress()),
                $startedAt->format(\DATE_ATOM),
                $job->getUpdatedAt()->format(\DATE_ATOM),
            ];
        }

        $io->table(['Name', 'Type', 'Progress', 'Started at', 'Last update'], $rows);

        return 0;
    }
}
