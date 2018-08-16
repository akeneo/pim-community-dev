<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Price interface (backend type entity)
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductPriceInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * Gets the price amount.
     *
     * @return float
     */
    public function getData();

    /**
     * Gets the used currency.
     *
     * @return string $currency
     */
    public function getCurrency();

    /**
     * Checks if the price is equal to another one.
     *
     * @param ProductPriceInterface $price
     *
     * @return bool
     */
    public function isEqual(ProductPriceInterface $price);
}
