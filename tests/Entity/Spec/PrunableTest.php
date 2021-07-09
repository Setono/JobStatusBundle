<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Entity\Spec;

use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\Prunable;

/**
 * @covers \Setono\JobStatusBundle\Entity\Spec\Prunable
 */
final class PrunableTest extends TestCase
{
    /**
     * @param bool $filtered true if the job satisfies the spec
     * @dataProvider getJobs
     * @test
     */
    public function it_filters(JobInterface $job, bool $filtered): void
    {
        $spec = new Prunable(new \DateTime('-30 days'));
        self::assertSame($filtered, $spec->isSatisfiedBy($job));
    }

    /**
     * @return iterable<array-key, array{0: JobInterface, 1: bool}>
     */
    public function getJobs(): iterable
    {
        $job = new Job();
        $job->setUpdatedAt(new \DateTime('-40 days'));
        yield [$job, true];

        $job = new Job();
        $job->setUpdatedAt(new \DateTime('-29 days'));
        yield [$job, false];

        $job = new Job();
        $job->setState(JobInterface::STATE_RUNNING);
        $job->setUpdatedAt(new \DateTime('-39 days'));
        yield [$job, false];
    }
}
