<?php

namespace Pim\Component\Resource\Persister;

use Pim\Component\Resource\Model\ResourceInterface;
use Pim\Component\Resource\Model\ResourceSetInterface;

/**
 * Resource manager interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourcePersisterInterface
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
     * @param bool                 $andFlush
     */
    public function bulkSave(ResourceSetInterface $resources, $andFlush = true);

    /**
     * Deletes several resources at the same time.
     * Note that a heavy collection of resources can lead to performances problems. Please split your
     * large collection of resources before proceeding to a bulk operation.
     *
     * @param ResourceSetInterface $resources
     * @param bool                 $andFlush
     */
    public function bulkDelete(ResourceSetInterface $resources, $andFlush = true);

    /**
     * Creates a set of resources from an array.
     *
     * @param array $resources
     *
     * @return ResourceSetInterface
     */
    public function createResourceSet(array $resources);

    /**
     * This is a transitional method. For internal purpose only. This method will allow us to use the
     * object manager for special cases that we do not handle correctly at the moment.
     *
     * TODO: delete this method once everything has been cleaned.
     *
     * @param string $class
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getObjectManagerTransitional($class);
}
