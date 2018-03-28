<?php

namespace Pim\Component\Catalog\Value;

use Pim\Component\Catalog\Model\PriceCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Interface for price collection product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PriceCollectionValueInterface extends ValueInterface
{
    /**
     * @return PriceCollectionInterface|null
     */
    public function getData();

    /**
     * @param string $currency
     *
     * @return PriceCollectionInterface|null
     */
    public function getPrice($currency);
}
