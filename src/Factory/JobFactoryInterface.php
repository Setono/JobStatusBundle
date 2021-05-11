<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Factory;

use Setono\JobStatusBundle\Entity\JobInterface;

interface JobFactoryInterface
{
    public function createNew(): JobInterface;
}
