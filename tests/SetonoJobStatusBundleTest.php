<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests;

use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Setono\JobStatusBundle\SetonoJobStatusBundle;

final class SetonoJobStatusBundleTest extends BaseBundleTestCase
{
    protected function getBundleClass(): string
    {
        return SetonoJobStatusBundle::class;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->addCompilerPass(new PublicServicePass('#setono.*#i'));
    }

    /**
     * @test
     */
//    public function it_has_services(): void
//    {
//        $this->bootKernel();
//        $container = $this->getContainer();
//
//        $services = [
//            // cookie.xml
//            'setono_client_id.cookie.adapter.symfony_cookie_reader' => [
//                'class' => SymfonyCookieReader::class,
//                'interface' => CookieReaderInterface::class,
//            ],
//
//            // generator.xml
//            ClientIdGeneratorInterface::class => [
//                'class' => UuidClientIdGenerator::class,
//                'interface' => ClientIdGeneratorInterface::class,
//            ],
//        ];
//
//        foreach ($services as $id => $data) {
//            self::assertTrue($container->has($id), sprintf('Container does not have service "%s"', $id));
//
//            /** @var object $service */
//            $service = $container->get($id);
//
//            if (isset($data['class'])) {
//                self::assertInstanceOf(
//                    $data['class'],
//                    $service,
//                    sprintf('Service with id "%s" is not an instance of %s. It is an instance of %s', $id, $data['class'], get_class($service))
//                );
//            }
//
//            if (isset($data['interface'])) {
//                self::assertInstanceOf(
//                    $data['interface'],
//                    $service,
//                    sprintf('Service with id "%s" is not an instance of %s. It is an instance of %s', $id, $data['interface'], get_class($service))
//                );
//            }
//        }
//    }
}
