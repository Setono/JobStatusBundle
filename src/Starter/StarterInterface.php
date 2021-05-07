<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Starter;

use Setono\JobStatusBundle\Entity\Job;

interface StarterInterface
{
    /**
     * If you don't supply a job, this service will create one for you. The job returned will either be this newly
     * created job OR the job you have as input
     *
     * NOTICE that it will only set the steps variable if the given argument is NOT null
     *
     * @param bool $flush Flushes the object manager if set to true
     */
    public function start(Job $job = null, int $steps = null, bool $flush = false): Job;
}
