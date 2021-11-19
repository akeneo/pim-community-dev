<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextAttributeHydrator extends AbstractAttributeHydrator
{
    public function supports(array $row): bool
    {
        return isset($row['attribute_type']) && 'text' === $row['attribute_type'];
    }

    public function convertAdditionalProperties(AbstractPlatform $platform, array $row): array
    {
        $row['is_textarea'] = Type::getType(Types::BOOLEAN)->convertToPhpValue($row['additional_properties']['is_textarea'], $platform);
        $row['max_length'] = Type::getType(Types::INTEGER)->convertToPhpValue($row['additional_properties']['max_length'], $platform);
        if (true === $row['additional_properties']['is_textarea']) {
            $row['is_rich_text_editor'] = Type::getType(Types::BOOLEAN)->convertToPhpValue($row['additional_properties']['is_rich_text_editor'], $platform);
        } else {
            $row['validation_rule'] = Type::getType(Types::STRING)->convertToPhpValue($row['additional_properties']['validation_rule'], $platform);
            $row['regular_expression'] = Type::getType(Types::STRING)->convertToPhpValue($row['additional_properties']['regular_expression'], $platform);
        }

        return $row;
    }

    protected function hydrateAttribute(array $row): AbstractAttribute
    {
        if (true === $row['is_textarea']) {
            return $this->hydrateTextArea($row);
        }

        return $this->hydrateSimpleText($row);
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
            'max_length',
            'is_textarea',
            'validation_rule',
            'regular_expression',
            'is_rich_text_editor'
        ];
    }

    private function hydrateTextArea(array $row): TextAttribute
    {
        $maxLength = null === $row['max_length'] ?
            AttributeMaxLength::noLimit()
            : AttributeMaxLength::fromInteger($row['max_length']);

        return TextAttribute::createTextarea(
            AttributeIdentifier::fromString($row['identifier']),
            ReferenceEntityIdentifier::fromString($row['reference_entity_identifier']),
            AttributeCode::fromString($row['code']),
            LabelCollection::fromArray($row['labels']),
            AttributeOrder::fromInteger($row['attribute_order']),
            AttributeIsRequired::fromBoolean($row['is_required']),
            AttributeValuePerChannel::fromBoolean($row['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($row['value_per_locale']),
            $maxLength,
            AttributeIsRichTextEditor::fromBoolean($row['additional_properties']['is_rich_text_editor'])
        );
    }

    private function hydrateSimpleText($row): TextAttribute
    {
        $maxLength = null === $row['max_length'] ?
            AttributeMaxLength::noLimit()
            : AttributeMaxLength::fromInteger($row['max_length']);
        $validationRule = null === $row['validation_rule'] ?
            AttributeValidationRule::none()
            : AttributeValidationRule::fromString($row['validation_rule']);
        $regularExpression = null === $row['regular_expression'] ?
            AttributeRegularExpression::createEmpty()
            : AttributeRegularExpression::fromString($row['regular_expression']);

        return TextAttribute::createText(
            AttributeIdentifier::fromString($row['identifier']),
            ReferenceEntityIdentifier::fromString($row['reference_entity_identifier']),
            AttributeCode::fromString($row['code']),
            LabelCollection::fromArray($row['labels']),
            AttributeOrder::fromInteger($row['attribute_order']),
            AttributeIsRequired::fromBoolean($row['is_required']),
            AttributeValuePerChannel::fromBoolean($row['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($row['value_per_locale']),
            $maxLength,
            $validationRule,
            $regularExpression
        );
    }
}
