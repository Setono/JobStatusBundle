<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Setono\JobStatusBundle\Entity\JobInterface;

/**
 * This class will make the Job entity an actual entity instead
 * of a mapped superclass if it wasn't extended by the end user
 */
final class ConvertMappedSuperclassEventListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var class-string $class */
        $class = $eventArgs->getClassMetadata()->getName();

        if (is_a($class, JobInterface::class, true)) {
            $eventArgs->getClassMetadata()->isMappedSuperclass = false;
        }
    }
}
