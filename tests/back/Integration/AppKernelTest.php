<?php

/**
 * App kernel for the integration tests.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppKernelTest extends AppKernel
{
    /**
     * {@inheritDoc}
     *
     * Necessary to make gedmo extension tree work. Otherwise the path located in
     * "vendor/akeneo/pim-community-dev/src/Akeneo/Platform/config/bundles/gedmo_doctrine_extensions.yml"
     * is never the right one...
     */
    public function getRootDir(): string
    {
        return $this->getProjectDir() . DIRECTORY_SEPARATOR . 'app';
    }
}
