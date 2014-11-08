<?php

namespace Pim\Component\Resource\Model;

/**
 * Updater interface, provides a minimal contract to update a single business object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdaterInterface
{
    /**
     * Update a single object
     *
     * @param mixed $object  The object to update
     * @param array $options The update options
     *
     * @throws \InvalidArgumentException
     */
    public function update($object, array $options = []);
}
