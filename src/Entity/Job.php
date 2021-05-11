<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity;

use DateTime;
use DateTimeInterface;
use Webmozart\Assert\Assert;

class Job implements JobInterface
{
    protected ?int $id = null;

    protected int $version = 1;

    protected string $type = 'generic';

    protected string $name = 'Generic job';

    protected string $state = self::STATE_PENDING;

    protected DateTimeInterface $createdAt;

    protected DateTimeInterface $updatedAt;

    protected ?DateTimeInterface $startedAt = null;

    protected ?DateTimeInterface $failedAt = null;

    protected ?DateTimeInterface $finishedAt = null;

    protected int $step = 0;

    protected ?int $steps = null;

    protected array $metadata = [];

    protected ?string $error = null;

    public function __construct()
    {
        $this->createdAt = $this->updatedAt = new DateTime();
    }

    /**
     * @return array<array-key, string>
     */
    public static function getStates(): array
    {
        return [
            self::STATE_PENDING, self::STATE_RUNNING, self::STATE_FAILED, self::STATE_FINISHED,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): int
    {
        return $this->version;
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

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * A name for the job to easily identify the job for the end user, examples could be:
     *
     * - Process Google shopping feed (id: 123)
     * - Update product prices on all products
     * - Crawl example.com for 404 errors
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        Assert::oneOf($state, self::getStates());

        $this->state = $state;
    }

    public function isRunning(): bool
    {
        return $this->state === self::STATE_RUNNING;
    }

    public function isFailed(): bool
    {
        return $this->state === self::STATE_FAILED;
    }

    public function isFinished(): bool
    {
        return $this->state === self::STATE_FINISHED;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * The last time this job was updated in any way
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
     * The current step, i.e. 45 out of 125, 45 is the step
     */
    public function getStep(): int
    {
        return $this->step;
    }

    public function setStep(int $step): void
    {
        Assert::greaterThanEq($step, 0);
        if (null !== $this->steps) {
            Assert::lessThanEq($step, $this->steps);
        }

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

    public function advance(int $steps = 1): void
    {
        $step = max(0, $this->step + $steps);

        if (null !== $this->steps) {
            $step = min($step, $this->steps);
        }

        $this->step = $step;
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

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function hasMetadataEntry(string $key): bool
    {
        return array_key_exists($key, $this->metadata);
    }

    /**
     * Notice that this method will overwrite any data saved on $key
     *
     * @param mixed $value
     */
    public function setMetadataEntry(string $key, $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }
}
