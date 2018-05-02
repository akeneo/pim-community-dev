<?php

namespace Akeneo\Tool\Component\StorageUtils\Updater;

/**
 * Sets a property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertySetterInterface
{
    /**
     * Sets a data in a object property (erase the current data)
     *
     * @param object $object   The object to update
     * @param string $property The property to update
     * @param mixed  $data     The data to set
     * @param array  $options  Options to pass to the setter
     *
     * @throws \InvalidArgumentException
     *
     * @return PropertySetterInterface
     */
    public function setData($object, $property, $data, array $options = []);
}
