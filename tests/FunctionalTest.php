<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EventSauce\BackOff\FibonacciBackOffStrategy;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\EventSubscriber\CheckJobFinishedEventSubscriber;
use Setono\JobStatusBundle\EventSubscriber\UpdateJobProgressEventSubscriber;
use Setono\JobStatusBundle\EventSubscriber\Workflow\FinishJobEventSubscriber;
use Setono\JobStatusBundle\EventSubscriber\Workflow\StartJobEventSubscriber;
use Setono\JobStatusBundle\Factory\JobFactory;
use Setono\JobStatusBundle\Manager\JobManager;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class FunctionalTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function a_job_starts_and_finishes(): void
    {
        $job = new Job();

        $eventDispatcher = new EventDispatcher();

        $objectManager = $this->prophesize(EntityManagerInterface::class);
        $objectManager->persist($job)->shouldBeCalledTimes(1);
        $objectManager->flush()->shouldBeCalledTimes(4);

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Job::class)->willReturn($objectManager);

        $workflowRegistry = self::getWorkflowRegistry($eventDispatcher);

        $jobRepository = $this->prophesize(JobRepositoryInterface::class);
        $jobRepository->hasExclusiveRunningJob('generic')->willReturn(false);

        $jobManager = new JobManager($workflowRegistry, $managerRegistry->reveal(), $jobRepository->reveal(), new JobFactory(21600), new FibonacciBackOffStrategy(250_000, 5));

        $eventDispatcher->addSubscriber(new CheckJobFinishedEventSubscriber($jobManager));
        $eventDispatcher->addSubscriber(new StartJobEventSubscriber());
        $eventDispatcher->addSubscriber(new FinishJobEventSubscriber());
        $eventDispatcher->addSubscriber(new UpdateJobProgressEventSubscriber($jobManager));

        $jobManager->start($job, 10);

        self::assertTrue($job->isRunning(), 'Job is not running');
        self::assertNotNull($job->getCreatedAt());
        self::assertNotNull($job->getUpdatedAt());
        self::assertNotNull($job->getStartedAt());

        $eventDispatcher->dispatch(new StepCompletedEvent($job, 5));
        $eventDispatcher->dispatch(new StepCompletedEvent($job, 5));

        self::assertTrue($job->isFinished(), 'Job is not finished');
        self::assertNotNull($job->getFinishedAt());
    }

    private static function getWorkflowRegistry(EventDispatcherInterface $eventDispatcher): Registry
    {
        $workflowRegistry = new Registry();
        $workflow = JobWorkflow::getWorkflow($eventDispatcher);
        $workflowRegistry->addWorkflow($workflow, new InstanceOfSupportStrategy(Job::class));

        return $workflowRegistry;
    }
}
