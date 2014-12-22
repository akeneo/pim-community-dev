<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Persistence;

/**
 * Detacher, detachs an object from its ObjectManager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : should be move in storage utils once https://github.com/akeneo/pim-community-dev/pull/1874 merged
 */
interface ObjectDetacherInterface
{
    /**
     * @param object $object
     */
    public function detach($object);
}