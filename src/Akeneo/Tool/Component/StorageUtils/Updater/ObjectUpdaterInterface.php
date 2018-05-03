<?php

namespace Akeneo\Tool\Component\StorageUtils\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * Updates an object with a set of data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ObjectUpdaterInterface
{
    /**
     * Updates an object (erase the current data)
     *
     * @param object $object  The object to update
     * @param array  $data    The data to update
     * @param array  $options The options to use
     *
     * @throws PropertyException
     *
     * @return ObjectUpdaterInterface
     */
    public function update($object, array $data, array $options = []);
}
