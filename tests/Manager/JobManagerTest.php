<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use EventSauce\BackOff\FibonacciBackOffStrategy;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Promise\PromiseInterface;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Factory\JobFactory;
use Setono\JobStatusBundle\Manager\JobManager;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Setono\JobStatusBundle\Workflow\JobWorkflow;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;

/**
 * @covers \Setono\JobStatusBundle\Manager\JobManager
 */
final class JobManagerTest extends TestCase
{
    use ProphecyTrait;

    private ?JobInterface $currentJob = null;

    /**
     * @test
     */
    public function it_starts_without_given_job(): void
    {
        $jobManager = $this->getJobManager();
        $job = $jobManager->start();

        self::assertTrue($job->isRunning());
    }

    /**
     * @test
     */
    public function it_starts_with_given_job(): void
    {
        $jobManager = $this->getJobManager();
        $job = new Job();
        $jobManager->start($job);

        self::assertTrue($job->isRunning());
    }

    /**
     * @test
     */
    public function it_advances(): void
    {
        $jobManager = $this->getJobManager();
        $job = new Job();
        $jobManager->advance($job);

        self::assertSame(1, $job->getStep());
    }

    /**
     * @test
     */
    public function it_advances_with_back_off_strategy(): void
    {
        $jobManager = $this->getJobManager($this->getObjectManager(3));
        $job = new Job();
        $this->currentJob = $job;
        $jobManager->advance($job);

        // the reason we know the step should be 4 is because the loop in JobManager reaches the catch part of the
        // try-catch 3 times and this means the JobManager::advance method is called 4 times in total
        self::assertSame(4, $job->getStep());
    }

    private function getJobManager(EntityManagerInterface $objectManager = null): JobManagerInterface
    {
        if (null === $objectManager) {
            $objectManager = $this->getObjectManager();
        }

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Job::class)->willReturn($objectManager);

        $jobRepository = $this->prophesize(JobRepositoryInterface::class);
        $jobRepository->hasExclusiveRunningJob('generic')->willReturn(false);

        $jobFactory = new JobFactory(21600);

        $backOffStrategy = new FibonacciBackOffStrategy(250_000, 5);

        return new JobManager(self::getWorkflowRegistry(), $managerRegistry->reveal(), $jobRepository->reveal(), $jobFactory, $backOffStrategy);
    }

    /**
     * @param int|null $throwOptimisticLockException the number of times to throw the optimistic lock exception
     */
    private function getObjectManager(int $throwOptimisticLockException = null): EntityManagerInterface
    {
        $objectManager = $this->prophesize(EntityManagerInterface::class);
        if (null !== $throwOptimisticLockException) {
            $promise = new class($throwOptimisticLockException) implements PromiseInterface {
                private int $timesThrown = 0;

                private int $timesToThrow;

                public function __construct(int $timesToThrow)
                {
                    $this->timesToThrow = $timesToThrow;
                }

                public function execute(array $args, ObjectProphecy $object, MethodProphecy $method)
                {
                    $this->timesThrown++;

                    if ($this->timesThrown >= $this->timesToThrow) {
                        $object->flush()->willReturn(null);
                    }

                    $object->refresh(Argument::type(JobInterface::class))->shouldBeCalled();

                    throw new OptimisticLockException('Optimistic lock exception thrown', null);
                }
            };

            $objectManager->flush()->will($promise);
        }

        return $objectManager->reveal();
    }

    private static function getWorkflowRegistry(): Registry
    {
        $eventDispatcher = new EventDispatcher();

        $workflowRegistry = new Registry();
        $workflow = JobWorkflow::getWorkflow($eventDispatcher);
        $workflowRegistry->addWorkflow($workflow, new InstanceOfSupportStrategy(Job::class));

        return $workflowRegistry;
    }
}
