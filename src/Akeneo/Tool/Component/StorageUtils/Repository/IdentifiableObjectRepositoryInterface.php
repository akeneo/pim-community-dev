<?php

namespace Akeneo\Tool\Component\StorageUtils\Repository;

/**
 * Interface for repositories of unique code objects
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IdentifiableObjectRepositoryInterface
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
     * @param string $identifier
     *
     * @return mixed
     */
    public function findOneByIdentifier($identifier);
}
