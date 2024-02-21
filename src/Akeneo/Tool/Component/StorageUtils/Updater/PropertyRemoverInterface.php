<?php

namespace Akeneo\Tool\Component\StorageUtils\Updater;

/**
 * Removes data in the property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertyRemoverInterface
{
    /**
     * Removes data in an object property (only provided data will be removed)
     *
     * @param object $object   The object to update
     * @param string $property The property to update
     * @param mixed  $data     The data to remove
     * @param array  $options  Options to pass to the remover
     *
     * @throws \InvalidArgumentException
     *
     * @return PropertyRemoverInterface
     */
    public function removeData($object, $property, $data, array $options = []);
}
