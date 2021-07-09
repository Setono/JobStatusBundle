<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Entity\Spec;

use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\NotUpdatedSince;

/**
 * @covers \Setono\JobStatusBundle\Entity\Spec\NotUpdatedSince
 */
final class NotUpdatedSinceTest extends TestCase
{
    /**
     * @param bool $filtered true if the job satisfies the spec
     * @dataProvider getJobs
     * @test
     */
    public function it_filters(JobInterface $job, bool $filtered): void
    {
        $spec = new NotUpdatedSince(new \DateTime('-15 minutes'));
        self::assertSame($filtered, $spec->isSatisfiedBy($job));
    }

    /**
     * @return iterable<array-key, array{0: JobInterface, 1: bool}>
     */
    public function getJobs(): iterable
    {
        $job = new Job();
        $job->setUpdatedAt(new \DateTime());
        yield [$job, false];

        $job = new Job();
        $job->setUpdatedAt(new \DateTime('-20 minutes'));
        yield [$job, true];
    }
}
