<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkeneoJobBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
    }
}
