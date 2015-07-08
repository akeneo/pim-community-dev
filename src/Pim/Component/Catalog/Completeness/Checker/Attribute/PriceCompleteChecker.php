<?php

namespace Pim\Component\Catalog\Completeness\Checker\Attribute;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCompleteChecker implements AttributeCompleteCheckerInterface
{
    public function isComplete(ProductValueInterface $value, ChannelInterface $channel, $localeCode = null)
    {
        $expectedCurrencies = array_map(
            function ($currency) {
                return $currency->getCode();
            },
            $channel->getCurrencies()->toArray()
        );
        foreach ($expectedCurrencies as $currency) {
            foreach ($value->getData() as $price) {
                if ($price->getCurrency() === $currency) {
                    if ($price->getData() === null) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return 'pim_catalog_price_collection' === $attribute->getAttributeType();
    }
}
