<?php

namespace EnterprisePim\Bundle\EnrichBundle\Twig;

use EnterprisePim\Bundle\CatalogBundle\Version;

/**
 * Extension to display version of the Akeneo
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        return 'enterprise_pim_version_extension';
    }
}
