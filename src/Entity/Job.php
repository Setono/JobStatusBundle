<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity;

use DateTimeInterface;
use Webmozart\Assert\Assert;

class Job
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_FAILED = 'failed';

    public const STATUS_FINISHED = 'finished';

    protected ?int $id = null;

    protected string $type = 'generic';

    protected string $status = self::STATUS_PENDING;

    protected ?DateTimeInterface $startedAt = null;

    protected ?DateTimeInterface $failedAt = null;

    protected ?DateTimeInterface $finishedAt = null;

    protected ?DateTimeInterface $updatedAt = null;

    protected int $step = 0;

    protected ?int $steps = null;

    protected array $metadata = [];

    protected ?string $error = null;

    /**
     * @return array<array-key, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING, self::STATUS_RUNNING, self::STATUS_FAILED, self::STATUS_FINISHED,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Use the type to distinguish between jobs
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        Assert::oneOf($status, self::getStatuses());

        $this->status = $status;
    }

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeInterface $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getFailedAt(): ?DateTimeInterface
    {
        return $this->failedAt;
    }

    public function setFailedAt(?DateTimeInterface $failedAt): void
    {
        $this->failedAt = $failedAt;
    }

    public function getFinishedAt(): ?DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?DateTimeInterface $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * The last time this job was updated in any way
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * The current step, i.e. 45 out of 125, 45 is the step
     */
    public function getStep(): int
    {
        return $this->step;
    }

    public function setStep(int $step): void
    {
        Assert::greaterThanEq($step, 0);

        $this->step = $step;
    }

    /**
     * The total number of steps. If null, we don't know the total number of steps
     */
    public function getSteps(): ?int
    {
        return $this->steps;
    }

    public function setSteps(?int $steps): void
    {
        Assert::nullOrGreaterThanEq($steps, 1);

        $this->steps = $steps;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    /**
     * Returns the progress in percent
     *
     * 376 of 1000 will return 37 with $decimals = 0
     *
     * If we can't compute a progress (because the steps are not set) it will return null
     */
    public function getProgress(int $decimals = 0): ?float
    {
        $steps = $this->getSteps();
        if (null === $steps) {
            return null;
        }

        return round(($this->getStep() / $steps) * 100, $decimals, \PHP_ROUND_HALF_DOWN);
    }
}
