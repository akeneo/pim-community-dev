<?php

namespace Akeneo\Tool\Component\StorageUtils\Remover;

/**
 * Remover interface, provides a minimal contract to remove a single business object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RemoverInterface
{
    /**
     * Delete a single object
     *
     * @param mixed $object  The object to delete
     * @param array $options The delete options
     *
     * @throws \InvalidArgumentException
     */
    public function remove($object, array $options = []);
}
