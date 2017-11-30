<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;

/**
 * Merge not granted data to an object
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface NotGrantedDataMergerInterface
{
    /**
     * @param mixed      $filteredItem
     * @param mixed|null $fullItem
     *
     * @return mixed
     *
     * @throws InvalidObjectException
     */
    public function merge($filteredItem, $fullItem = null);
}
