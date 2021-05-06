<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\JobStatusBundle\DependencyInjection\SetonoJobStatusExtension;

/**
 * @covers \Setono\JobStatusBundle\DependencyInjection\SetonoJobStatusExtension
 */
final class SetonoJobStatusExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new SetonoJobStatusExtension(),
        ];
    }

    /**
     * @test
     */
    public function it_can_load(): void
    {
        $this->load();

        self::assertTrue(true);
    }
}
