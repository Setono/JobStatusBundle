<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Factory;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;

final class JobFactory implements JobFactoryInterface
{
    private int $defaultWaitForTimeout;

    public function __construct(int $defaultWaitForTimeout)
    {
        $this->defaultWaitForTimeout = $defaultWaitForTimeout;
    }

    public function createNew(): JobInterface
    {
        $job = new Job();
        $job->setWaitForTimeout($this->defaultWaitForTimeout);

        $pid = getmypid();
        if (false !== $pid) {
            $job->addPid($pid);
        }

        return $job;
    }
}
