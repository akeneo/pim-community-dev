<?php

namespace Pim\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Filter the product values according to currency codes provided in options.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCurrencyFilter extends AbstractFilter implements ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$productValue instanceof ProductValueInterface) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        $currencies = isset($options['currencies']) ? $options['currencies'] : [];
        $prices     = $productValue->getPrices();

        if (0 === $prices->count()) {
            return false;
        }

        foreach ($prices as $price) {
            if (!in_array($price->getCurrency(), $currencies)) {
                $productValue->removePrice($price);
            }
        }

        return false;
    }
}
