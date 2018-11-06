<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class OptionCollectionAttributeHydrator extends AbstractAttributeHydrator
{
    private const ATTRIBUTE_TYPE = 'option_collection';

    public function supports(array $row): bool
    {
        return isset($row['attribute_type']) && self::ATTRIBUTE_TYPE === $row['attribute_type'];
    }

    public function convertAdditionalProperties(AbstractPlatform $platform, array $row): array
    {
        $row['options'] = $row['additional_properties']['options'];

        return $row;
    }

    public function hydrateAttribute(array $row): AbstractAttribute
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString($row['identifier']),
            ReferenceEntityIdentifier::fromString($row['reference_entity_identifier']),
            AttributeCode::fromString($row['code']),
            LabelCollection::fromArray($row['labels']),
            AttributeOrder::fromInteger($row['attribute_order']),
            AttributeIsRequired::fromBoolean($row['is_required']),
            AttributeValuePerChannel::fromBoolean($row['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($row['value_per_locale'])
        );
        $attributeOptions = $this->hydrateOptions($row['options']);
        $optionAttribute->setOptions($attributeOptions);

        return $optionAttribute;
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
            'options',
        ];
    }

    /**
     * @param array $attributeOptions
     *
     * @return AttributeOption[]
     */
    private function hydrateOptions(array $attributeOptions): array
    {
        return array_map(function (array $attributeOption) {
            return AttributeOption::create(
                OptionCode::fromString($attributeOption['code']),
                LabelCollection::fromArray($attributeOption['labels'])
            );
        }, $attributeOptions);
    }
}
