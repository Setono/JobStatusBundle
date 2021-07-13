<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Setono\JobStatusBundle\Entity\Job;
use Setono\JobStatusBundle\Entity\JobInterface;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * This test validates the job.html.twig template
 */
final class JobTemplateTest extends TestCase
{
    /**
     * @test
     */
    public function it_renders_running_job(): void
    {
        $job = new Job();
        $job->setState(JobInterface::STATE_RUNNING);
        $job->setStartedAt(\DateTime::createFromFormat('Y-m-d H:i:s', '2021-07-13 10:14:05'));
        $job->setUpdatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', '2021-07-13 10:25:55'));
        $job->setSteps(100);
        $job->setStep(12);

        $tds = $this->html($job)->filter('table > tbody > tr > td');

        self::assertCount(8, $tds);
        self::assertSame('Generic job', $tds->eq(0)->text()); // name
        self::assertSame('generic', $tds->eq(1)->text()); // type
        self::assertSame('running', $tds->eq(2)->text()); // state
        self::assertSame('12 / 100 (12%)', $tds->eq(3)->text()); // progress
        self::assertMatchesRegularExpression('/[0-9]+ seconds left/', $tds->eq(4)->text()); // eta
        self::assertSame('July 13, 2021 10:14', $tds->eq(5)->text()); // started at
        self::assertSame('July 13, 2021 10:25', $tds->eq(6)->text()); // last update
        self::assertSame('Not finished yet', $tds->eq(7)->text()); // finished at
    }

    private function html(JobInterface $job): Crawler
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../src/Resources/views', 'SetonoJobStatus');
        $twig = new Environment($loader);

        return new Crawler($twig->render('@SetonoJobStatus/job.html.twig', ['job' => $job]));
    }
}
