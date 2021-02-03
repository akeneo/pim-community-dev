<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\TwigExtension;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ExternalJavascriptDependenciesProvider;

class ExternalJavascriptDependenciesExtension extends \Twig_Extension
{
    private ExternalJavascriptDependenciesProvider $dependenciesProvider;

    public function __construct(ExternalJavascriptDependenciesProvider $dependenciesProvider)
    {
        $this->dependenciesProvider = $dependenciesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('external_javascript_dependencies', [$this, 'getExternalJavascriptDependencies']),
        ];
    }

    public function getExternalJavascriptDependencies(): string
    {
        return join(' ', $this->dependenciesProvider->getScripts());
    }
}
