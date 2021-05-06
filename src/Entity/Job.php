<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="setono_job_status__job")
 */
class Job
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_FAILED = 'failed';

    public const STATUS_FINISHED = 'finished';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected ?int $id = null;

    /**
     * Mapping this field as a version field will ensure that step updates won't overwrite each other
     *
     * @ORM\Column(type="integer")
     * @ORM\Version()
     */
    protected int $version = 1;

    /** @ORM\Column(type="string") */
    protected string $type = 'generic';

    /** @ORM\Column(type="string") */
    protected string $status = self::STATUS_PENDING;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected ?DateTimeInterface $startedAt = null;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected ?DateTimeInterface $failedAt = null;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected ?DateTimeInterface $finishedAt = null;

    /** @ORM\Column(type="datetime") */
    protected ?DateTimeInterface $updatedAt = null;

    /** @ORM\Column(type="integer") */
    protected int $step = 0;

    /** @ORM\Column(type="integer", nullable=true) */
    protected ?int $steps = null;

    /** @ORM\Column(type="array") */
    protected array $metadata = [];

    /** @ORM\Column(type="text", nullable=true) */
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

    public function advance(int $steps = 1): void
    {
        $this->step += $steps;
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
