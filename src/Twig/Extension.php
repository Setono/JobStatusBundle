<?php

declare(strict_types=1);

namespace Setono\JobStatusBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sjs_last_job_by_type', [Runtime::class, 'findLastJobByType']),
        ];
    }
}
