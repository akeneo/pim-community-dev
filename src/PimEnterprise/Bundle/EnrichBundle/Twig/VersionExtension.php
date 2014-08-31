<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Twig;

use PimEnterprise\Bundle\CatalogBundle\Version;

/**
 * Extension to display version of the Akeneo
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class VersionExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'enterprise_version' => new \Twig_Function_Method($this, 'version'),
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_version_extension';
    }
}
