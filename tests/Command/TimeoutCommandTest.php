<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Command\TimeoutCommand;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Setono\JobStatusBundle\Command\TimeoutCommand
 */
final class TimeoutCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_executes(): void
    {
        $job = new Job();

        $jobRepository = $this->prophesize(JobRepositoryInterface::class);
        $jobRepository->match(Argument::any())->willReturn([$job]);
        $jobManager = $this->prophesize(JobManagerInterface::class);

        $commandTester = new CommandTester(new TimeoutCommand($jobRepository->reveal(), $jobManager->reveal()));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("1 job was transitioned to the 'timed_out' state", $output);
    }
}
