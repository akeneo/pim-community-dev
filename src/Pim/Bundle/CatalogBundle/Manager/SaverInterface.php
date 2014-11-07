<?php

namespace Pim\Bundle\CatalogBundle\Manager;

/**
 * Saver interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SaverInterface
{
    /**
     * Save a single object
     *
     * @param mixed $object  The object to save
     * @param array $options The saving options
     */
    public function save($object, array $options = []);

    /**
     * Save multiple objects
     *
     * @param array $objects The objects to save
     * @param array $options The saving options
     */
    public function saveAll(array $objects, array $options = []);
}
