<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\VersionProviderInterface;

/**
 * Extension to display version of the Akeneo
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionExtension extends \Twig_Extension
{
    /** @var VersionProviderInterface */
    private $versionProvider;

    public function __construct(VersionProviderInterface $versionProvider)
    {
        $this->versionProvider = $versionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('version', fn() => $this->version()),
        ];
    }

    /**
     * Returns the current version
     */
    public function version(): string
    {
        return $this->versionProvider->getFullVersion();
    }
}
