<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Twig;

use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\Spec\LastJobWithType;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Setono\JobStatusBundle\Twig\Extension;
use Setono\JobStatusBundle\Twig\Runtime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Test\IntegrationTestCase;
use Webmozart\Assert\Assert;

/**
 * @covers \Setono\JobStatusBundle\Twig\Extension
 * @covers \Setono\JobStatusBundle\Twig\Runtime
 */
final class ExtensionTest extends IntegrationTestCase
{
    use ProphecyTrait;

    public function getRuntimeLoaders(): array
    {
        $job = new Job();

        $jobRepository = $this->prophesize(JobRepositoryInterface::class);
        $jobRepository->matchOneOrNullResult(new LastJobWithType('generic'))->willReturn($job);

        $runtimeLoader = new class($jobRepository->reveal()) implements RuntimeLoaderInterface {
            private JobRepositoryInterface $jobRepository;

            public function __construct(JobRepositoryInterface $jobRepository)
            {
                $this->jobRepository = $jobRepository;
            }

            /**
             * @param class-string|mixed $class
             */
            public function load($class): Runtime
            {
                Assert::same($class, Runtime::class);

                return new Runtime($this->jobRepository);
            }
        };

        return [$runtimeLoader];
    }

    public function getExtensions(): array
    {
        return [
            new Extension(),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/Fixtures/';
    }
}
