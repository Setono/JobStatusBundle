<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Entity;

use DateTimeInterface;

interface JobInterface
{
    public const STATE_PENDING = 'pending';

    public const STATE_RUNNING = 'running';

    public const STATE_FAILED = 'failed';

    public const STATE_FINISHED = 'finished';

    public function getId(): ?int;

    public function getVersion(): int;

    /**
     * Returns the PIDs of the processes involved in this job
     *
     * @return array<array-key, int>
     */
    public function getPids(): array;

    public function addPid(int $pid): void;

    public function getType(): string;

    /**
     * Use the type to distinguish between jobs
     */
    public function setType(string $type): void;

    public function getName(): string;

    /**
     * A name for the job to easily identify the job for the end user, examples could be:
     *
     * - Process Google shopping feed (id: 123)
     * - Update product prices on all products
     * - Crawl example.com for 404 errors
     */
    public function setName(string $name): void;

    /**
     * Returns true if this job is only allowed to run one at a time
     */
    public function isExclusive(): bool;

    public function setExclusive(bool $exclusive): void;

    public function getState(): string;

    public function setState(string $state): void;

    public function isRunning(): bool;

    public function isFailed(): bool;

    public function isFinished(): bool;

    public function getCreatedAt(): DateTimeInterface;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    /**
     * The last time this job was updated in any way
     */
    public function getUpdatedAt(): DateTimeInterface;

    public function setUpdatedAt(DateTimeInterface $updatedAt): void;

    public function getStartedAt(): ?DateTimeInterface;

    public function setStartedAt(?DateTimeInterface $startedAt): void;

    public function getFailedAt(): ?DateTimeInterface;

    public function setFailedAt(?DateTimeInterface $failedAt): void;

    public function getFinishedAt(): ?DateTimeInterface;

    public function setFinishedAt(?DateTimeInterface $finishedAt): void;

    /**
     * The current step, i.e. 45 out of 125, 45 is the step
     */
    public function getStep(): int;

    public function setStep(int $step): void;

    /**
     * The total number of steps. If null, we don't know the total number of steps
     */
    public function getSteps(): ?int;

    public function setSteps(?int $steps): void;

    public function advance(int $steps = 1): void;

    /**
     * Returns the progress in percent
     *
     * 376 of 1000 will return 37
     *
     * If we can't compute a progress (because the steps are not set) it will return null
     */
    public function getProgress(): ?int;

    public function getMetadata(): array;

    public function setMetadata(array $metadata): void;

    public function hasMetadataEntry(string $key): bool;

    /**
     * @throws \OutOfBoundsException if the key doesn't exist
     *
     * @return mixed
     */
    public function getMetadataEntry(string $key);

    /**
     * Notice that this method will overwrite any data saved on $key
     *
     * @param mixed $value
     */
    public function setMetadataEntry(string $key, $value): void;

    public function getError(): ?string;

    public function setError(?string $error): void;
}
