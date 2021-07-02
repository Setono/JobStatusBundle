<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Exception;

final class TimeoutCommandException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * @param array<array-key, string> $errors
     */
    public static function fromErrors(array $errors): self
    {
        $message = '';

        foreach ($errors as $error) {
            $message .= $error . "\n";
        }

        return new self($message);
    }
}
