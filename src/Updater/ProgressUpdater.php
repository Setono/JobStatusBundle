<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Updater;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use EventSauce\BackOff\BackOffStrategy;
use Setono\JobStatusBundle\Entity\Job;
use Webmozart\Assert\Assert;

final class ProgressUpdater implements ProgressUpdaterInterface
{
    private ?ObjectManager $manager = null;

    private ManagerRegistry $managerRegistry;

    private BackOffStrategy $backOffStrategy;

    public function __construct(ManagerRegistry $managerRegistry, BackOffStrategy $backOffStrategy)
    {
        $this->managerRegistry = $managerRegistry;
        $this->backOffStrategy = $backOffStrategy;
    }

    public function update(Job $job, int $steps = 1): void
    {
        $tries = 0;

        do {
            // this could look like a bug, but the $job is refreshed (in the object manager)
            // in the case self::flush returns false
            $job->advance($steps);
        } while (!$this->flush($job, ++$tries));
    }

    /**
     * Returns true if the manager flushes else it 'backs off' using
     * the given back off strategy and then refreshes the job
     *
     * @throws OptimisticLockException
     */
    private function flush(Job $job, int $tries): bool
    {
        $manager = $this->getManager($job);

        try {
            $manager->flush();
        } catch (OptimisticLockException $e) {
            $this->backOffStrategy->backOff($tries, $e);

            $manager->refresh($job);

            return false;
        }

        return true;
    }

    private function getManager(object $obj): ObjectManager
    {
        if (null === $this->manager) {
            $manager = $this->managerRegistry->getManagerForClass(get_class($obj));
            Assert::notNull($manager);

            $this->manager = $manager;
        }

        return $this->manager;
    }
}
