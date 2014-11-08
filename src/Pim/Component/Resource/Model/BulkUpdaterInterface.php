<?php

namespace Pim\Component\Resource\Model;

/**
 * Bulk updater interface, provides a minimal contract to update many business objects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkUpdaterInterface
{
    /**
     * Update many objects
     *
     * @param array $objects The objects to update
     * @param array $options The update options
     *
     * @throws \InvalidArgumentException
     */
    public function updateAll(array $objects, array $options = []);
}
