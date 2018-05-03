<?php

namespace Akeneo\Tool\Component\StorageUtils\Repository;

/**
 * Countable repository interface
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountableRepositoryInterface
{
    /**
     * Return the number of all entities
     *
     * @return int
     */
    public function countAll(): int;
}
