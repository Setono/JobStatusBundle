<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Entity\Spec;

use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\NotRunning;

/**
 * @covers \Setono\JobStatusBundle\Entity\Spec\NotRunning
 */
final class NotRunningTest extends TestCase
{
    /**
     * @param bool $filtered true if the job satisfies the spec
     * @dataProvider getJobs
     * @test
     */
    public function it_filters(JobInterface $job, bool $filtered): void
    {
        $spec = new NotRunning();
        self::assertSame($filtered, $spec->isSatisfiedBy($job));
    }

    /**
     * @return iterable<array-key, array{0: JobInterface, 1: bool}>
     */
    public function getJobs(): iterable
    {
        $job = new Job();
        $job->setState(JobInterface::STATE_RUNNING);
        yield [$job, false];

        $job = new Job();
        $job->setState(JobInterface::STATE_PENDING);
        yield [$job, true];

        $job = new Job();
        yield [$job, true];
    }
}
