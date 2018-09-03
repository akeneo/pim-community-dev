<?php

namespace Akeneo\Tool\Component\StorageUtils\Updater;

/**
 * Adds data in the property of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertyAdderInterface
{
    /**
     * Adds data in a object property (complete the current data)
     *
     * @param object $object   The object to update
     * @param string $property The property to update
     * @param mixed  $data     The data to add
     * @param array  $options  Options to pass to the adder
     *
     * @throws \InvalidArgumentException
     *
     * @return PropertyAdderInterface
     */
    public function addData($object, $property, $data, array $options = []);
}
