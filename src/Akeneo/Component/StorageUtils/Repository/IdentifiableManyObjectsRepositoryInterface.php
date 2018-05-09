<?php

namespace Akeneo\Component\StorageUtils\Repository;

/**
 * Interface IdentifiableManyObjectRepositoryInterface
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IdentifiableManyObjectsRepositoryInterface
{
    /**
     * Returns an array containing the name of the unique identifier properties
     *
     * @return array
     */
    public function getIdentifierProperties();

    /**
     * Find an object by its identifier
     *
     * @param array $identifiers
     *
     * @return mixed
     */
    public function findManyByIdentifier(array $identifiers);
}
