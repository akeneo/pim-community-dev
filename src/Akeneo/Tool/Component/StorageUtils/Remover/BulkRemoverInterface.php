<?php

namespace Akeneo\Tool\Component\StorageUtils\Remover;

/**
 * Bulk remover interface, provides a minimal contract to delete many business objects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkRemoverInterface
{
    /**
     * Delete many objects
     *
     * @param array $objects The objects to delete
     * @param array $options The delete options
     *
     * @throws \InvalidArgumentException
     */
    public function removeAll(array $objects, array $options = []);
}
