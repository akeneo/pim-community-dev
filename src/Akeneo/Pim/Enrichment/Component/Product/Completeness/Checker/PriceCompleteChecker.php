<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

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
 *           \Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteChecker
 *           to calculate the completeness on a product value
 */
class PriceCompleteChecker implements ValueCompleteCheckerInterface
{
    /** @var LruArrayAttributeRepository */
    protected $attributeRepository;

    public function __construct(LruArrayAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $expectedCurrencies = $channel->getCurrencies()->map(function ($currency) {
            return $currency->getCode();
        });

        $completeForCurrency = [];

        foreach ($expectedCurrencies as $currency) {
            $completeForCurrency[$currency] = false;
            foreach ($value->getData() as $price) {
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
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        return (null !== $attribute && AttributeTypes::PRICE_COLLECTION === $attribute->getType());
    }
}
