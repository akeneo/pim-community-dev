<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextAttributeHydrator extends AbstractAttributeHydrator
{
    public function supports(array $result): bool
    {
        return isset($result['attribute_type']) && 'text' === $result['attribute_type'];
    }

    public function hydrate(array $result)
    {
        $result = $this->hydrateCommonProperties($result);
        if (true === $result['additional_properties']['is_textarea']) {
            return $this->hydrateTextArea($result);
        }

        return $this->hydrateSimpleText($result);
    }

    private function hydrateTextArea(array $result): TextAttribute
    {
        $isRichTextEditor = $result['additional_properties']['is_rich_text_editor'];
        $maxLength = null === $result['additional_properties']['max_length'] ?
            AttributeMaxLength::noLimit()
            : AttributeMaxLength::fromInteger((int) $result['additional_properties']['max_length']);

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
            AttributeIsRichTextEditor::fromBoolean($isRichTextEditor)
        );
    }

    private function hydrateSimpleText($result): TextAttribute {
        $maxLength = null === $result['max_length'] ?
            AttributeMaxLength::noLimit() : AttributeMaxLength::fromInteger($result['max_length']);
        $validationRule = null === $result['additionnal_properties']['validation_rule'] ?
            AttributeValidationRule::none() : AttributeValidationRule::fromString($result['additionnal_properties']['validation_rule']);
        $regularExpression = null === $result['additionnal_properties']['regular_expression'] ?
            AttributeRegularExpression::createEmpty() : AttributeRegularExpression::fromString($result['additionnal_properties']['regular_expression']);

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
