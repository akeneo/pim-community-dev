<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Check if a product price collection complete or not for a provided channel.
 *
 * For the product value to be complete, it has to contain a price with an amount
 * for every currency activated in the channel.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal for internal use only, please use
 *           \Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker
 *           to calculate the completeness on a product value
 */
class PriceCompleteChecker implements ProductValueCompleteCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ProductValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $expectedCurrencies = $channel->getCurrencies()->map(function ($currency) {
            return $currency->getCode();
        });

        $completeForCurrency = [];

        foreach ($expectedCurrencies as $currency) {
            $completeForCurrency[$currency] = false;
            foreach ($productValue->getData() as $price) {
                if ($currency === $price->getCurrency() && null !== $price->getData()) {
                    $completeForCurrency[$currency] = true;
                }
            }
        }

        return !in_array(false, $completeForCurrency);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(
        ProductValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        return AttributeTypes::PRICE_COLLECTION === $productValue->getAttribute()->getType();
    }
}
