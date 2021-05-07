<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use EventSauce\BackOff\FibonacciBackOffStrategy;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Event\StepCompletedEvent;
use Setono\JobStatusBundle\EventListener\CheckJobFinishedEventSubscriber;
use Setono\JobStatusBundle\EventListener\UpdateJobProgressEventSubscriber;
use Setono\JobStatusBundle\EventListener\Workflow\FinishJobEventSubscriber;
use Setono\JobStatusBundle\EventListener\Workflow\StartJobEventSubscriber;
use Setono\JobStatusBundle\Finisher\Finisher;
use Setono\JobStatusBundle\Starter\Starter;
use Setono\JobStatusBundle\Updater\ProgressUpdater;
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

        $objectManager = $this->prophesize(ObjectManager::class);
        $objectManager->flush()->shouldBeCalledTimes(2);

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Job::class)->willReturn($objectManager);

        $workflowRegistry = self::getWorkflowRegistry($eventDispatcher);

        $finisher = new Finisher($workflowRegistry);

        $starter = new Starter($workflowRegistry);

        $progressUpdater = new ProgressUpdater($managerRegistry->reveal(), new FibonacciBackOffStrategy(250_000, 5));

        $eventDispatcher->addSubscriber(new CheckJobFinishedEventSubscriber($finisher));
        $eventDispatcher->addSubscriber(new StartJobEventSubscriber());
        $eventDispatcher->addSubscriber(new FinishJobEventSubscriber());
        $eventDispatcher->addSubscriber(new UpdateJobProgressEventSubscriber($progressUpdater));

        $starter->start($job, 10);

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
