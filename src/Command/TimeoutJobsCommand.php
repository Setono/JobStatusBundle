<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Command;

use Happyr\DoctrineSpecification\Spec;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TimeoutJobsCommand extends Command
{
    protected static $defaultName = 'setono:job-status:timeout';

    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        parent::__construct();

        $this->jobRepository = $jobRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
        ->andWhere('DATE_ADD(o.updatedAt, INTERVAL o.waitForTimeout SECOND) < :now')
        ->setParameter('now', new \DateTime())
         */
        do {
            $spec = Spec::andX(
                Spec::lt(Spec::DATE_ADD()),
                Spec::eq('state', JobInterface::STATE_RUNNING)
            );
            $jobs = $this->jobRepository->match();
        } while (count($jobs) > 0);

        return 0;
    }
}
