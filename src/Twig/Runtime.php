<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Twig;

use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\LastJobWithType;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class Runtime implements RuntimeExtensionInterface
{
    private JobRepositoryInterface $jobRepository;

    public function __construct(JobRepositoryInterface $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    public function findLastJobByType(string $type): ?JobInterface
    {
        /** @var JobInterface|null $job */
        $job = $this->jobRepository->matchOneOrNullResult(new LastJobWithType($type));

        return $job;
    }
}
