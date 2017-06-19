<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\CatalogBundle\Version;

/**
 * Extension to display version of the Akeneo
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('version', [$this, 'version']),
        ];
    }

    /**
     * Returns the current version
     *
     * @return string
     */
    public function version()
    {
        $version = Version::VERSION;
        if (Version::VERSION_CODENAME) {
            $version .= sprintf(' %s', Version::VERSION_CODENAME);
        }

        return $version;
    }
}
