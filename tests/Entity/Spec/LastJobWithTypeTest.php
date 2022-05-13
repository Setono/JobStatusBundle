<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Entity\Spec;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Setono\JobStatusBundle\Entity\Spec\LastJobWithType;
use Setono\JobStatusBundle\Repository\JobRepository;

/**
 * @covers \Setono\JobStatusBundle\Entity\Spec\LastJobWithType
 */
final class LastJobWithTypeTest extends TestCase
{
    use ProphecyTrait;

    private bool $databaseCreated = false;

    private EntityManagerInterface $entityManager;

    private ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        if (!$this->databaseCreated) {
            $fileLocator = new SymfonyFileLocator([
                __DIR__ . '/../../../src/Resources/config/doctrine' => 'Setono\JobStatusBundle\Entity',
            ], '.orm.xml');
            $config = ORMSetup::createXMLMetadataConfiguration([], true);
            $config->setMetadataDriverImpl(new XmlDriver($fileLocator));

            $this->entityManager = EntityManager::create([
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . '/db.sqlite',
            ], $config);

            $managerRegistry = $this->prophesize(ManagerRegistry::class);
            $managerRegistry->getManagerForClass(Job::class)->willReturn($this->entityManager);
            $this->managerRegistry = $managerRegistry->reveal();

            $classes = [
                $this->entityManager->getClassMetadata(Job::class),
            ];

            foreach ($classes as $class) {
                $class->isMappedSuperclass = false;
            }

            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->dropSchema($classes);
            $schemaTool->createSchema($classes);

            $loader = new Loader();
            $loader->loadFromDirectory(__DIR__ . '/../../Fixtures');

            $executor = new ORMExecutor($this->entityManager, new ORMPurger());
            $executor->execute($loader->getFixtures());
        }
    }

    /**
     * @test
     */
    public function it_finds_last_job_with_type(): void
    {
        $repository = new JobRepository($this->managerRegistry, Job::class);

        /** @var JobInterface|null $result */
        $result = $repository->matchOneOrNullResult(new LastJobWithType('specific_type'));

        self::assertNotNull($result);
        self::assertSame('Job #2', $result->getName());
    }
}
