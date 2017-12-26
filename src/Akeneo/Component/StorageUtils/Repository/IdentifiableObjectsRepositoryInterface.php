<?php

namespace Akeneo\Component\StorageUtils\Repository;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IdentifiableObjectsRepositoryInterface
{
    /**
     * Find an object by its identifier
     *
     * @param array $identifiers
     *
     * @return mixed
     */
    public function findSeveralByIdentifiers(array $identifiers);
}
