<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC;

final class ExternalJavascriptDependenciesProvider
{
    private iterable $externalDependenciesProviders;

    public function __construct(iterable $externalDependenciesProviders)
    {
        $this->externalDependenciesProviders = $externalDependenciesProviders;
    }

    public function getScripts(): array
    {
        $dependencies = [];

        foreach ($this->externalDependenciesProviders as $externalDependenciesProvider) {
            $dependencies[] = $externalDependenciesProvider->getScript();
        }

        return $dependencies;
    }
}
