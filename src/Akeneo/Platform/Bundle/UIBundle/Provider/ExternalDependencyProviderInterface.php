<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider;

interface ExternalDependencyProviderInterface
{
    public function getScript(): string;
}
