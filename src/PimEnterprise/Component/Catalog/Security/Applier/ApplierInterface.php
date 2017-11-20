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

namespace PimEnterprise\Component\Catalog\Security\Applier;

/**
 * Apply date from filtered item to the full item.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ApplierInterface
{
    /**
     * @param mixed $item
     *
     * @return mixed
     */
    public function apply($item);
}
