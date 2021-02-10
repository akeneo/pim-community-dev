<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalJavascriptDependenciesProvider;

final class ExternalJavascriptDependenciesExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('external_javascript_dependencies', [$this, 'getExternalJavascriptDependencies'], ['is_safe' => ['html']]),
        ];
    }

    public function getExternalJavascriptDependencies(): string
    {
        return join("\n", $this->dependenciesProvider->getScripts());
    }
}
