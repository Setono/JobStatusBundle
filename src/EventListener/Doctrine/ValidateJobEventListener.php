<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Setono\JobStatusBundle\Entity\JobInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidateJobEventListener
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->validate($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->validate($args);
    }

    private function validate(LifecycleEventArgs $args): void
    {
        $job = $args->getObject();
        if (!$job instanceof JobInterface) {
            return;
        }

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($job);
        if ($errors->count() > 0) {
            throw new \RuntimeException(sprintf('Job "%s" with id %d failed validation. Errors: %s', $job->getName(), (int) $job->getId(), (string) $errors));
        }
    }
}
