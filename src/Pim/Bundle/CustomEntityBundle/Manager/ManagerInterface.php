<?php

namespace Pim\Bundle\CustomEntityBundle\Manager;

/**
 * Base interface for custom entity managers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ManagerInterface
{
    /**
     * Create an entity
     *
     * @param string $entityClass
     * @param array  $defaultValues
     * @param array  $options
     *
     * @return object
     */
    public function create($entityClass, array $defaultValues = array(), array $options = array());

    /**
     * Find an entity by id, returns null if the object is not found
     *
     * @param string $entityClass
     * @param mixed  $id
     * @param array  $options
     *
     * @return object
     */
    public function find($entityClass, $id, array $options = array());

    /**
     * Saves the entity
     *
     * @param object $entity
     */
    public function save($entity);

    /**
     * Remove the entity
     *
     * @param object $entity
     */
    public function remove($entity);
}
