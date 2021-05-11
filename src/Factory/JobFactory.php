<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Factory;

use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;

final class JobFactory implements JobFactoryInterface
{
    public function createNew(): JobInterface
    {
        return new Job();
    }
}