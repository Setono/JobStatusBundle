<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Setono\JobStatusBundle\Entity\JobInterface;

final class ConvertMappedSuperclassEventListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $class = $eventArgs->getClassMetadata()->getName();

        if (is_a($class, JobInterface::class, true)) {
            $eventArgs->getClassMetadata()->isMappedSuperclass = false;
        }
    }
}
