<?php

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
 * Filter not granted data from object
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface NotGrantedDataFilterInterface
{
    /**
     * @param mixed $object
     *
     * @throws InvalidObjectException
     *
     * @return mixed
     */
    public function filter($object);
}
