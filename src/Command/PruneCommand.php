<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

final class PruneCommand extends Command
{
    use ORMManagerTrait;

    protected static $defaultName = 'setono:job-status:prune';

    protected static $defaultDescription = 'Prunes the jobs in the database by removing old entries';

    private JobRepositoryInterface $jobRepository;

    private int $defaultHours;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        ManagerRegistry $managerRegistry,
        int $defaultHours
    ) {
        $this->jobRepository = $jobRepository;
        $this->managerRegistry = $managerRegistry;
        $this->defaultHours = $defaultHours;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription((string) self::$defaultDescription)
            ->addOption(
                'hours',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the number of hours a job has to be to be pruned/removed',
                (string) $this->defaultHours
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var mixed $hours */
        $hours = $input->getOption('hours');
        Assert::integerish($hours);

        $hours = (int) $hours;

        $threshold = new \DateTimeImmutable(sprintf('-%d hours', $hours));
        $io->text(sprintf('Removes jobs not updated since %s', $threshold->format('Y-m-d H:i')));

        $jobs = $this->jobRepository->findNotUpdatedSince($threshold);

        $manager = null;
        $jobsRemoved = 0;
        foreach ($jobs as $job) {
            $jobsRemoved++;

            $manager = $this->getManager($job);
            $manager->remove($job);

            if ($jobsRemoved % 50 === 0) {
                $manager->flush();
                $manager->clear();
            }
        }

        if (null !== $manager) {
            $manager->flush();
        }

        $io->success(sprintf('%d jobs removed', $jobsRemoved));

        return 0;
    }
}
