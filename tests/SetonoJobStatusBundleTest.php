<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EventSauce\BackOff\BackOffStrategy;
use EventSauce\BackOff\FibonacciBackOffStrategy;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Setono\JobStatusBundle\SetonoJobStatusBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers \Setono\JobStatusBundle\SetonoJobStatusBundle
 */
final class SetonoJobStatusBundleTest extends BaseBundleTestCase
{
    protected function getBundleClass(): string
    {
        return SetonoJobStatusBundle::class;
    }

    public function testBundleWithDifferentConfiguration(): void
    {
        // Create a new Kernel
        $kernel = new TestKernel('dev', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $services = [
            'setono_job_status.back_off.fibonacci' => [
                'interface' => BackOffStrategy::class,
                'class' => FibonacciBackOffStrategy::class,
            ]
        ];

        foreach ($services as $id => $values) {
            self::assertTrue($container->has($id));

            $service = $container->get($id);
            self::assertNotNull($service);

            self::assertSame($values['class'], get_class($service));
            self::assertInstanceOf($values['interface'], $service);
        }
    }
}

class TestKernel extends Kernel
{
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PublicServicePass());
    }

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new TwigBundle(), new DoctrineBundle(), new SetonoJobStatusBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container
                ->loadFromExtension('framework', [
                    'annotations' => [
                        'cache' => 'file',
                        'file_cache_dir' => '%kernel.cache_dir%/annotations'
                    ],
                    'secret' => '$ecret',
                    'serializer' => [
                        'enabled' => true,
                    ],
                    'router' => [
                        'utf8' => true,
                        'resource' => 'kernel::loadRoutes'
                    ]
                ])
                ->loadFromExtension('doctrine', [
                    'dbal' => [
                        'url' => '%env(resolve:DATABASE_URL)%'
                    ],
                ])
            ;
        });
    }

    /**
     * @psalm-suppress MixedOperand
     */
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/' . Kernel::VERSION . '/SetonoJobStatusBundleTestKernel/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/' . Kernel::VERSION . '/SetonoJobStatusBundleTestKernel/logs';
    }
}
