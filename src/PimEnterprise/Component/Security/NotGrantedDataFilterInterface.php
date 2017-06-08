<?php

namespace PimEnterprise\Component\Security;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;

/**
 * Filter not granted data from object
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
