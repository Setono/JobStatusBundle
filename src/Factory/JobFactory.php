<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Factory;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;

final class JobFactory implements JobFactoryInterface
{
    private int $defaultTtl;

    public function __construct(int $defaultTtl)
    {
        $this->defaultTtl = $defaultTtl;
    }

    public function createNew(): JobInterface
    {
        $job = new Job();
        $job->setTtl($this->defaultTtl);

        $pid = getmypid();
        if (false !== $pid) {
            $job->addPid($pid);
        }

        return $job;
    }
}
