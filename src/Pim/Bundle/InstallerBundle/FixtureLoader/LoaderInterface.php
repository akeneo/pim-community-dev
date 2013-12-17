<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

/**
 * Fixture loader interface
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LoaderInterface
{
    public function load($file);
}
