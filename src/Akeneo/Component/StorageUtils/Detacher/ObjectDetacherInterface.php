<?php

namespace Akeneo\Component\StorageUtils\Detacher;

/**
 * Detacher, detaches an object from its ObjectManager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @api
 */
interface ObjectDetacherInterface
{
    /**
     * @param object $object
     *
     * @api
     */
    public function detach($object);
}
