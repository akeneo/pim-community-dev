<?php

namespace Akeneo\Component\StorageUtils\Detacher;

/**
 * Bulk detacher, detaches many objects from their ObjectManager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @api
 */
interface BulkObjectDetacherInterface
{
    /**
     * @param array $objects
     *
     * @api
     */
    public function detachAll(array $objects);
}
