<?php

namespace Pim\Bundle\EnrichBundle\Provider\StructureVersion;

/**
 * Structure version provider interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StructureVersionProviderInterface
{
    /**
     * Returns the last version of the structure which the provider is responsible
     *
     * @return int The current structure version number
     */
    public function getStructureVersion();
}
