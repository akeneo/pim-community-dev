<?php

namespace Pim\Bundle\VersioningBundle\Entity;

/**
 * VersionableInterface interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionableInterface
{
    /**
     * @return int
     */
    public function getVersion();

    /**
     * @return array
     */
    public function getVersionedData();

    /**
     * @return int
     *
    public function getResourceId();
    */
}