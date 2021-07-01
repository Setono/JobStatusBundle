<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Entity\Job;

/**
 * @covers \Setono\JobStatusBundle\Entity\Job
 */
final class JobTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_eta(): void
    {
        $job = new Job();
        $job->setStep(10);
        $job->setSteps(100);

        // steps left = 90

        $job->setStartedAt(new \DateTime('-60 minutes'));
        $job->setUpdatedAt(new \DateTime('-10 minutes'));

        // seconds per step = (50 minutes * 60 seconds / 10 steps) = 300
        // eta = (90 * 300) - (10 minutes * 60 seconds) = 26400

        $eta = $job->getEta();

        self::assertSame(26400, $eta);
    }

    /**
     * @test
     */
    public function it_recomputes_times_out_at_when_updating_entity(): void
    {
        $job = new Job();
        $job->setTtl(3600);
        $job->setUpdatedAt(new \DateTime('-100 seconds'));

        $expected = new \DateTime('+3500 seconds');
        $timesOutAt = $job->getTimesOutAt();

        self::assertNotNull($timesOutAt);
        self::assertSame($expected->getTimestamp(), $timesOutAt->getTimestamp());
    }

    /**
     * @test
     */
    public function it_recomputes_times_out_at_when_updating_ttl(): void
    {
        $job = new Job();
        $job->setTtl(100);

        $expected = new \DateTime('+100 seconds');
        $timesOutAt = $job->getTimesOutAt();

        self::assertNotNull($timesOutAt);
        self::assertSame($expected->getTimestamp(), $timesOutAt->getTimestamp());
    }
}
