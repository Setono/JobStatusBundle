<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Starter;

use Setono\JobStatusBundle\Entity\Job;

interface StarterInterface
{
    public function start(Job $job): void;
}
