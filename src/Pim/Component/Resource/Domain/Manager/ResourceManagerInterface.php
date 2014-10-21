<?php


namespace Pim\Component\Resource\Domain\Manager;

use Pim\Component\Resource\Domain\ResourceInterface;
use Pim\Component\Resource\Domain\ResourceSetInterface;

/**
 * Resource manager interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceManagerInterface
{
    /**
     * Saves (creates or updates) a resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $andFlush
     */
    public function save(ResourceInterface $resource, $andFlush = true);

    /**
     * Deletes a resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $andFlush
     */
    public function delete(ResourceInterface $resource, $andFlush = true);

    /**
     * Saves several resources at the same time.
     * Note that a heavy collection of resources can lead to performances problems. Please split your
     * large collection of resources before proceeding to a bulk operation.
     *
     * @param ResourceSetInterface $resources
     * @param bool              $andFlush
     */
    public function bulkSave(ResourceSetInterface $resources, $andFlush = true);

    /**
     * Deletes several resources at the same time.
     * Note that a heavy collection of resources can lead to performances problems. Please split your
     * large collection of resources before proceeding to a bulk operation.
     *
     * @param ResourceSetInterface $resources
     * @param bool              $andFlush
     */
    public function bulkDelete(ResourceSetInterface $resources, $andFlush = true);
}
