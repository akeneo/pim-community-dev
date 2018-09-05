<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextAttributeHydrator extends AbstractAttributeHydrator
{
    private const EXPECTED_KEYS = [
        'identifier',
        'enriched_entity_identifier',
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

    public function supports(array $result): bool
    {
        return isset($result['attribute_type']) && 'text' === $result['attribute_type'];
    }

    protected function checkResult(array $result): void
    {
        $actualKeys = array_keys($result);
        if (isset($result['additional_properties'])) {
            $actualKeys = array_merge(
                $actualKeys,
                array_keys(json_decode($result['additional_properties'], true))
            );
            unset($result['additional_properties']);
        }

        $missingInformation = array_diff(self::EXPECTED_KEYS, $actualKeys);
        $canHydrate = 0 === count($missingInformation);
        if (!$canHydrate) {
            throw new \RuntimeException(
                sprintf(
                    'Impossible to hydrate the text attribute because some information is missing: %s',
                    implode(', ', $missingInformation)
                )
            );
        }
    }

    public function convertAdditionalProperties(AbstractPlatform $platform, array $result): array
    {
        $result['is_textarea'] = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['additional_properties']['is_textarea'], $platform);
        $result['max_length'] = Type::getType(Type::INTEGER)->convertToPhpValue($result['additional_properties']['max_length'], $platform);
        if (true === $result['additional_properties']['is_textarea']) {
            $result['is_rich_text_editor'] = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['additional_properties']['is_rich_text_editor'], $platform);
        } else {
            $result['validation_rule'] = Type::getType(Type::STRING)->convertToPhpValue($result['additional_properties']['validation_rule'], $platform);
            $result['regular_expression'] = Type::getType(Type::STRING)->convertToPhpValue($result['additional_properties']['regular_expression'], $platform);
        }

        return $result;
    }

    protected function hydrateAttribute(array $result): AbstractAttribute
    {
        if (true === $result['is_textarea']) {
            return $this->hydrateTextArea($result);
        }

        return $this->hydrateSimpleText($result);
    }

    private function hydrateTextArea(array $result): TextAttribute
    {
        $maxLength = null === $result['max_length'] ?
            AttributeMaxLength::noLimit()
            : AttributeMaxLength::fromInteger($result['max_length']);

        return TextAttribute::createTextarea(
            AttributeIdentifier::fromString($result['identifier']),
            EnrichedEntityIdentifier::fromString($result['enriched_entity_identifier']),
            AttributeCode::fromString($result['code']),
            LabelCollection::fromArray($result['labels']),
            AttributeOrder::fromInteger($result['attribute_order']),
            AttributeIsRequired::fromBoolean($result['is_required']),
            AttributeValuePerChannel::fromBoolean($result['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($result['value_per_locale']),
            $maxLength,
            AttributeIsRichTextEditor::fromBoolean($result['additional_properties']['is_rich_text_editor'])
        );
    }

    private function hydrateSimpleText($result): TextAttribute
    {
        $maxLength = null === $result['max_length'] ?
            AttributeMaxLength::noLimit()
            : AttributeMaxLength::fromInteger($result['max_length']);
        $validationRule = null === $result['validation_rule'] ?
            AttributeValidationRule::none()
            : AttributeValidationRule::fromString($result['validation_rule']);
        $regularExpression = null === $result['regular_expression'] ?
            AttributeRegularExpression::createEmpty()
            : AttributeRegularExpression::fromString($result['regular_expression']);

        return TextAttribute::createText(
            AttributeIdentifier::fromString($result['identifier']),
            EnrichedEntityIdentifier::fromString($result['enriched_entity_identifier']),
            AttributeCode::fromString($result['code']),
            LabelCollection::fromArray($result['labels']),
            AttributeOrder::fromInteger($result['attribute_order']),
            AttributeIsRequired::fromBoolean($result['is_required']),
            AttributeValuePerChannel::fromBoolean($result['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($result['value_per_locale']),
            $maxLength,
            $validationRule,
            $regularExpression
        );
    }
}
