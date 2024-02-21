<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NumberToMetricDataConverter implements ValueDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function supportsAttributes(AttributeInterface $sourceAttribute, AttributeInterface $targetAttribute): bool
    {
        return AttributeTypes::NUMBER === $sourceAttribute->getType() &&
            AttributeTypes::METRIC === $targetAttribute->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function convert(ValueInterface $sourceValue, AttributeInterface $targetAttribute)
    {
        Assert::numeric($sourceValue->getData());
        Assert::notNull($targetAttribute->getDefaultMetricUnit());

        return [
            'amount' => $sourceValue->getData(),
            'unit' => $targetAttribute->getDefaultMetricUnit(),
        ];
    }
}
