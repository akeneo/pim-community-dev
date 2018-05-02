<?php

namespace Akeneo\Tool\Component\StorageUtils\Saver;

/**
 * Bulk saver interface, provides a minimal contract to save many business objects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkSaverInterface
{
    /**
     * Save many objects
     *
     * @param array $objects The objects to save
     * @param array $options The saving options
     *
     * @throws \InvalidArgumentException
     */
    public function saveAll(array $objects, array $options = []);
}
