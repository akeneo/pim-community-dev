<?php

namespace Akeneo\Tool\Component\StorageUtils\Repository;

/**
 * Interface for cursorable repositories
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CursorableRepositoryInterface
{
    /**
     * @param array $identifiers
     *
     * @return array
     */
    public function getItemsFromIdentifiers(array $identifiers);
}
