<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EventSauce\BackOff\BackOffStrategy;
use EventSauce\BackOff\FibonacciBackOffStrategy;
use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Command\ListCommand;
use Setono\JobStatusBundle\Command\PruneCommand;
use Setono\JobStatusBundle\Command\TimeoutCommand;
use Setono\JobStatusBundle\EventListener\Doctrine\ConvertMappedSuperclassEventListener;
use Setono\JobStatusBundle\EventListener\Doctrine\ValidateJobEventListener;
use Setono\JobStatusBundle\EventSubscriber\CheckJobFinishedEventSubscriber;
use Setono\JobStatusBundle\EventSubscriber\UpdateJobProgressEventSubscriber;
use Setono\JobStatusBundle\EventSubscriber\Workflow\FinishJobEventSubscriber;
use Setono\JobStatusBundle\EventSubscriber\Workflow\StartJobEventSubscriber;
use Setono\JobStatusBundle\Factory\JobFactory;
use Setono\JobStatusBundle\Factory\JobFactoryInterface;
use Setono\JobStatusBundle\Manager\JobManager;
use Setono\JobStatusBundle\Manager\JobManagerInterface;
use Setono\JobStatusBundle\Repository\JobRepository;
use Setono\JobStatusBundle\Repository\JobRepositoryInterface;
use Setono\JobStatusBundle\SetonoJobStatusBundle;
use Setono\JobStatusBundle\Twig\Extension;
use Setono\JobStatusBundle\Twig\Runtime;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Extension\ExtensionInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @covers \Setono\JobStatusBundle\SetonoJobStatusBundle
 */
final class SetonoJobStatusBundleTest extends TestCase
{
    protected function getBundleClass(): string
    {
        return SetonoJobStatusBundle::class;
    }

    /**
     * @test
     */
    public function it_has_services(): void
    {
        $kernel = new TestKernel('dev', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $services = [
            // back_off.xml
            'setono_job_status.back_off.fibonacci' => [
                'interface' => BackOffStrategy::class,
                'class' => FibonacciBackOffStrategy::class,
            ],

            // command.xml
            'setono_job_status.command.list' => [
                'class' => ListCommand::class,
            ],
            'setono_job_status.command.prune' => [
                'class' => PruneCommand::class,
            ],
            'setono_job_status.command.timeout' => [
                'class' => TimeoutCommand::class,
            ],

            // event_listener.xml
            'setono_job_status.event_listener.doctrine.validate_job' => [
                'class' => ValidateJobEventListener::class,
            ],
            'setono_job_status.event_listener.doctrine.convert_mapped_superclass' => [
                'class' => ConvertMappedSuperclassEventListener::class,
            ],

            // event_subscriber.xml
            'setono_job_status.event_subscriber.check_job_finished_event_subscriber' => [
                'class' => CheckJobFinishedEventSubscriber::class,
                'interface' => EventSubscriberInterface::class,
            ],
            'setono_job_status.event_subscriber.update_job_progress_event_subscriber' => [
                'class' => UpdateJobProgressEventSubscriber::class,
                'interface' => EventSubscriberInterface::class,
            ],
            'setono_job_status.event_subscriber.workflow.finish_job_event_subscriber' => [
                'class' => FinishJobEventSubscriber::class,
                'interface' => EventSubscriberInterface::class,
            ],
            'setono_job_status.event_subscriber.workflow.start_job_event_subscriber' => [
                'class' => StartJobEventSubscriber::class,
                'interface' => EventSubscriberInterface::class,
            ],

            // factory.xml
            'setono_job_status.factory.job' => [
                'class' => JobFactory::class,
                'interface' => JobFactoryInterface::class,
            ],

            // manager.xml
            'setono_job_status.manager.job' => [
                'class' => JobManager::class,
                'interface' => JobManagerInterface::class,
            ],

            // repository.xml
            'setono_job_status.repository.job' => [
                'class' => JobRepository::class,
                'interface' => JobRepositoryInterface::class,
            ],

            // twig.xml
            'setono_job_status.twig.extension' => [
                'class' => Extension::class,
                'interface' => ExtensionInterface::class,
            ],
            'setono_job_status.twig.runtime' => [
                'class' => Runtime::class,
                'interface' => RuntimeExtensionInterface::class,
            ],
        ];

        foreach ($services as $id => $values) {
            self::assertTrue($container->has($id));

            $service = $container->get($id);
            self::assertNotNull($service);

            self::assertSame($values['class'], get_class($service));

            if (isset($values['interface'])) {
                self::assertInstanceOf($values['interface'], $service);
            }
        }
    }
}

class TestKernel extends Kernel
{
    protected function build(ContainerBuilder $container): void
    {
        // this compiler pass will make all services public
        // which makes it possible to get a service using $container->get()
        $compilerPass = new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $definition) {
                    $definition->setPublic(true);
                }

                foreach ($container->getAliases() as $alias) {
                    $alias->setPublic(true);
                }
            }
        };

        $container->addCompilerPass($compilerPass);
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
                        'file_cache_dir' => '%kernel.cache_dir%/annotations',
                    ],
                    'secret' => '$ecret',
                    'serializer' => [
                        'enabled' => true,
                    ],
                    'router' => [
                        'utf8' => true,
                        'resource' => 'kernel::loadRoutes',
                    ],
                ])
                ->loadFromExtension('doctrine', [
                    'dbal' => [
                        'url' => 'sqlite:///%kernel.project_dir%/var/data.db',
                    ],
                    'orm' => [
                        'auto_generate_proxy_classes' => true,
                        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                        'auto_mapping' => true,
                        'mappings' => [
                            'SetonoJobStatusBundle' => [
                                'is_bundle' => false,
                                'type' => 'xml',
                                'dir' => __DIR__ . '/../src/Resources/config/doctrine',
                                'prefix' => 'Setono\JobStatusBundle\Entity',
                            ],
                        ],
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
