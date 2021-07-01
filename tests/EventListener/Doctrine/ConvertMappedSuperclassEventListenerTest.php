<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\EventListener\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\EventListener\Doctrine\ConvertMappedSuperclassEventListener;

/**
 * @covers \Setono\JobStatusBundle\EventListener\Doctrine\ConvertMappedSuperclassEventListener
 */
final class ConvertMappedSuperclassEventListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_converts_mapped_superclass_to_entity(): void
    {
        $classMetadata = new ClassMetadata(Job::class);
        $classMetadata->isMappedSuperclass = true;

        $objectManager = $this->prophesize(ObjectManager::class);
        $eventArgs = new LoadClassMetadataEventArgs($classMetadata, $objectManager->reveal());
        $listener = new ConvertMappedSuperclassEventListener();
        $listener->loadClassMetadata($eventArgs);

        /** @psalm-suppress TypeDoesNotContainType */
        self::assertFalse($classMetadata->isMappedSuperclass);
    }
}
