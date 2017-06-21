<?php

namespace Pim\Component\Catalog\Completeness\Checker;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Check if a metric collection data is complete or not.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal for internal use only, please use
 *           \Pim\Component\Catalog\Completeness\Checker\ValueCompleteChecker
 *           to calculate the completeness on a product value
 */
class MetricCompleteChecker implements ValueCompleteCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $metric = $value->getData();

        if (null === $metric) {
            return false;
        }

        if (null === $metric->getData() ||
            null === $metric->getBaseData() ||
            null === $metric->getUnit() ||
            null === $metric->getBaseUnit() ||
            '' === $metric->getData() ||
            '' === $metric->getBaseData() ||
            '' === $metric->getUnit() ||
            '' === $metric->getBaseUnit()
        ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        return AttributeTypes::METRIC === $value->getAttribute()->getType();
    }
}
