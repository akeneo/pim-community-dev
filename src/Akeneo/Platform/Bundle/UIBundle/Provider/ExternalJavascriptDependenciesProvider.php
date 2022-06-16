<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider;

final class ExternalJavascriptDependenciesProvider
{
    private iterable $externalDependenciesProviders;

    public function __construct(iterable $externalDependenciesProviders)
    {
        $this->externalDependenciesProviders = $externalDependenciesProviders;
    }

    public function getScripts(): ?array
    {
        $dependencies = [];

        foreach ($this->externalDependenciesProviders as $externalDependenciesProvider) {
            if ($externalDependenciesProvider->getScript() !== null) {
                $dependencies[] = $externalDependenciesProvider->getScript();
            }
        }

        if (empty($dependencies)) {
            return null;
        }

        return $dependencies;
    }
}
