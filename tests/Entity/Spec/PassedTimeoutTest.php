<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Entity\Spec;

use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\PassedTimeout;

/**
 * @covers \Setono\JobStatusBundle\Entity\Spec\PassedTimeout
 */
final class PassedTimeoutTest extends TestCase
{
    /**
     * @param bool $filtered true if the job satisfies the spec
     * @dataProvider getJobs
     * @test
     */
    public function it_filters(JobInterface $job, bool $filtered): void
    {
        $spec = new PassedTimeout();
        self::assertSame($filtered, $spec->isSatisfiedBy($job));
    }

    /**
     * @return iterable<array-key, array{0: JobInterface, 1: bool}>
     */
    public function getJobs(): iterable
    {
        $job = new Job();
        $job->setTimesOutAt(new \DateTime('-10 minutes'));
        $job->setState(JobInterface::STATE_RUNNING);

        yield [$job, true];

        $job = new Job();
        $job->setTimesOutAt(new \DateTime('+10 minutes'));
        $job->setState(JobInterface::STATE_RUNNING);

        yield [$job, false];

        $job = new Job();
        $job->setTimesOutAt(new \DateTime('-10 minutes'));
        $job->setState(JobInterface::STATE_PENDING);

        yield [$job, false];
    }
}
