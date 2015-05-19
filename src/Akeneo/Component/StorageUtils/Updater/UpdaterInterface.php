<?php

namespace Akeneo\Component\StorageUtils\Updater;

/**
 * Updates an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdaterInterface
{
    /**
     * Updates an object (erase the current data)
     *
     * @param object $object  The object to update
     * @param array  $data    The data to update
     * @param array  $options The options to use
     *
     * @throws \InvalidArgumentException
     *
     * @return UpdaterInterface
     */
    public function update($object, array $data, array $options = []);
}
