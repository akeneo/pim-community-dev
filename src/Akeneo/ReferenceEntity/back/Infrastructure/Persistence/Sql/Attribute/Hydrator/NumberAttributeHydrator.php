<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsDecimal;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttributeHydrator extends AbstractAttributeHydrator
{
    public function supports(array $row): bool
    {
        return isset($row['attribute_type']) && 'number' === $row['attribute_type'];
    }

    public function convertAdditionalProperties(AbstractPlatform $platform, array $row): array
    {
        $row['is_decimal'] = $row['additional_properties']['is_decimal'];
        $row['min_value'] = $row['additional_properties']['min_value'];
        $row['max_value'] = $row['additional_properties']['max_value'];

        return $row;
    }

    protected function hydrateAttribute(array $row): AbstractAttribute
    {
        return NumberAttribute::create(
            AttributeIdentifier::fromString($row['identifier']),
            ReferenceEntityIdentifier::fromString($row['reference_entity_identifier']),
            AttributeCode::fromString($row['code']),
            LabelCollection::fromArray($row['labels']),
            AttributeOrder::fromInteger($row['attribute_order']),
            AttributeIsRequired::fromBoolean($row['is_required']),
            AttributeValuePerChannel::fromBoolean($row['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($row['value_per_locale']),
            AttributeIsDecimal::fromBoolean($row['is_decimal']),
            $this->minValue($row),
            $this->maxValue($row)
        );
    }

    protected function getExpectedProperties(): array
    {
        return [
            'identifier',
            'reference_entity_identifier',
            'code',
            'labels',
            'attribute_order',
            'is_required',
            'value_per_locale',
            'value_per_channel',
            'attribute_type',
            'is_decimal',
            'min_value',
            'max_value'
        ];
    }

    private function minValue(array $row): AttributeLimit
    {
        $min = $row['min_value'];

        return null !== $min ? AttributeLimit::fromString($min) : AttributeLimit::limitless();
    }

    private function maxValue(array $row): AttributeLimit
    {
        $max = $row['max_value'];

        return null !== $max ? AttributeLimit::fromString($max) : AttributeLimit::limitless();
    }
}
