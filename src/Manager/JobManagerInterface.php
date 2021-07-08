<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Manager;

use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Exception\CannotStartJobException;

interface JobManagerInterface
{
    /**
     * If you don't supply a job, this service will create one for you. The job returned will either be this newly
     * created job OR the job you have as input
     *
     * NOTICE that it will _only_ set the steps variable if the given argument is NOT null
     *
     * @param bool $flush if true, flushes the object manager
     *
     * @throws CannotStartJobException if it's not possible to start the job
     */
    public function start(JobInterface $job = null, int $steps = null, bool $flush = true): JobInterface;

    /**
     * @param bool $flush if true, flushes the object manager
     */
    public function finish(JobInterface $job, bool $flush = true): void;

    /**
     * Will transition the job to the timed out state
     *
     * @param bool $flush if true, flushes the object manager
     */
    public function timeout(JobInterface $job, bool $flush = true): void;

    /**
     * Will advance the job by the given steps
     */
    public function advance(JobInterface $job, int $steps = 1, bool $flush = true): void;
}
