<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity;

use DateTimeInterface;
use Webmozart\Assert\Assert;

class Job implements JobInterface
{
    protected ?int $id = null;

    protected int $version = 1;

    /** @var array<array-key, int> */
    protected array $pids = [];

    protected string $type = 'generic';

    protected string $name = 'Generic job';

    protected bool $exclusive = false;

    protected string $state = self::STATE_PENDING;

    protected DateTimeInterface $createdAt;

    protected DateTimeInterface $updatedAt;

    protected ?DateTimeInterface $startedAt = null;

    protected ?DateTimeInterface $failedAt = null;

    protected ?DateTimeInterface $finishedAt = null;

    protected ?DateTimeInterface $timesOutAt = null;

    protected int $ttl = 0;

    protected int $step = 0;

    protected ?int $steps = null;

    protected array $metadata = [];

    protected ?string $error = null;

    public function __construct()
    {
        $now = new \DateTime();
        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return array<array-key, string>
     */
    public static function getStates(): array
    {
        return [
            self::STATE_PENDING, self::STATE_RUNNING, self::STATE_FAILED, self::STATE_TIMED_OUT, self::STATE_FINISHED,
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

    public function getPids(): array
    {
        return $this->pids;
    }

    public function addPid(int $pid): void
    {
        $this->pids[] = $pid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function setExclusive(bool $exclusive): void
    {
        $this->exclusive = $exclusive;
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

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;

        $this->recomputeTimesOutAt();
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

    public function getEta(): ?int
    {
        // if we don't know the number of steps or haven't completed a single step yet, we can't compute the eta
        if (null === $this->steps || 0 === $this->step || null === $this->startedAt) {
            return null;
        }

        $stepsLeft = $this->steps - $this->step;
        $secondsPerStep = (int) ceil(($this->updatedAt->getTimestamp() - $this->startedAt->getTimestamp()) / $this->step);

        return ($stepsLeft * $secondsPerStep) - ((new \DateTime())->getTimestamp() - $this->updatedAt->getTimestamp());
    }

    public function getTimesOutAt(): ?DateTimeInterface
    {
        return $this->timesOutAt;
    }

    public function setTimesOutAt(?DateTimeInterface $timesOutAt): void
    {
        $this->timesOutAt = $timesOutAt;
    }

    public function recomputeTimesOutAt(): void
    {
        if (0 === $this->ttl) {
            $this->timesOutAt = null;

            return;
        }

        try {
            $updatedAt = clone $this->getUpdatedAt();
            if (!$updatedAt instanceof \DateTime && !$updatedAt instanceof \DateTimeImmutable) {
                throw new \InvalidArgumentException('The updatedAt value is not an instance of either DateTime or DateTimeImmutable');
            }

            $this->timesOutAt = $updatedAt->add(new \DateInterval(sprintf('PT%dS', $this->ttl)));
        } catch (\Throwable $e) {
            $this->timesOutAt = null;
        }
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): void
    {
        Assert::greaterThanEq($ttl, 0);

        $this->ttl = $ttl;

        $this->recomputeTimesOutAt();
    }

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

    public function getProgress(): ?int
    {
        $steps = $this->getSteps();
        if (null === $steps) {
            return null;
        }

        return (int) floor($this->getStep() / $steps * 100);
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

    public function getMetadataEntry(string $key)
    {
        if (!$this->hasMetadataEntry($key)) {
            throw new \OutOfBoundsException(sprintf('The key "%s" does not exist in the metadata', $key));
        }

        return $this->metadata[$key];
    }

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
