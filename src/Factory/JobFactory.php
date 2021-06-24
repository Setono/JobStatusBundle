<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Factory;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;

final class JobFactory implements JobFactoryInterface
{
    public function createNew(): JobInterface
    {
        $job = new Job();
        $job->setTimeout(new \DateTime('+24 hours'));

        return $job;
    }
}
