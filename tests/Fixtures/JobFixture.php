<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Setono\JobStatusBundle\Entity\Job;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class JobFixture implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach (self::jobs() as $data) {
            $job = new Job();

            /** @var mixed $value */
            foreach ($data as $property => $value) {
                $propertyAccessor->setValue($job, $property, $value);
            }

            $manager->persist($job);
        }

        $manager->flush();
    }

    /**
     * @return iterable<array<string, mixed>>
     * @psalm-suppress UnusedVariable
     *
     * Do not change the order of jobs because tests can easily be written by asserting the job name because that is unique
     */
    private static function jobs(): iterable
    {
        $i = 0;

        yield [
            'name' => sprintf('Job #%d', ++$i),
            'type' => 'specific_type',
            'startedAt' => new \DateTime('-10 minutes'),
        ];

        yield [
            'name' => sprintf('Job #%d', ++$i),
            'type' => 'specific_type',
            'startedAt' => new \DateTime('-5 minutes'),
        ];

        yield [
            'name' => sprintf('Job #%d', ++$i),
            'type' => 'specific_type',
            'startedAt' => new \DateTime('-15 minutes'),
        ];
    }
}
