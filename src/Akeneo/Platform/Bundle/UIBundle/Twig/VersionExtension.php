<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension to display version of the Akeneo
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionExtension extends AbstractExtension
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
    public function getFunctions()
    {
        return [
            new TwigFunction('version', [$this, 'version']),
        ];
    }

    /**
     * Returns the current version
     *
     * @return string
     */
    public function version()
    {
        return $this->versionProvider->getFullVersion();
    }
}
