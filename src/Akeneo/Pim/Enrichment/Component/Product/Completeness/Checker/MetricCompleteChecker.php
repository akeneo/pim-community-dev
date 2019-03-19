<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Check if a metric collection data is complete or not.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal for internal use only, please use
 *           \Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteChecker
 *           to calculate the completeness on a product value
 */
class MetricCompleteChecker implements ValueCompleteCheckerInterface
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
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        return (null !== $attribute && AttributeTypes::METRIC === $attribute->getType());
    }
}
