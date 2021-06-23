<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Exception;

final class CannotStartJobException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function exclusiveJobRunning(string $type): self
    {
        return new self(sprintf('An exclusive job with the type "%s" is already running', $type));
    }

    public static function transitionBlocked(string $transition): self
    {
        return new self(sprintf('The transition "%s" was blocked in the workflow', $transition));
    }
}
