<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Command\ListCommand;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\Spec\Running;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Setono\JobStatusBundle\Command\ListCommand
 */
final class ListCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_executes(): void
    {
        $dt = new \DateTime('-10 minutes');

        $job = new Job();
        $job->setStartedAt($dt);
        $job->setUpdatedAt($dt);

        $jobRepository = $this->prophesize(JobRepositoryInterface::class);
        $jobRepository->match(new Running())->willReturn([$job]);

        $commandTester = new CommandTester(new ListCommand($jobRepository->reveal()));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('1 running job', $output);
    }
}
