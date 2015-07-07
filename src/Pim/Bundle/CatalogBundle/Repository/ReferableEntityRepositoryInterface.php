<?php

namespace Pim\Bundle\CatalogBundle\Repository;

/**
 * Interface for repositories of unique code entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.4, please use
 *             Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface instead
 */
interface ReferableEntityRepositoryInterface
{
    /**
     * Returns an array containing the name of the unique code propertieS
     *
     * @return array
     *
     * @deprecated will be removed in 1.4
     */
    public function getReferenceProperties();

    /**
     * Find an entity by unique code
     *
     * @param string $code
     *
     * @return object
     *
     * @deprecated will be removed in 1.4
     */
    public function findByReference($code);
}
