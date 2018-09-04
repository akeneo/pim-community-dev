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
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextAttributeHydrator extends AbstractAttributeHydrator
{
    public const EXPECTED_KEYS = [
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

    public function hydrate(AbstractPlatform $platform, array $result)
    {
        $this->checkResult($result);

        $result = $this->hydrateCommonProperties($platform, $result);
        if (true === $result['additional_properties']['is_textarea']) {
            return $this->hydrateTextArea($result, $platform);
        }

        return $this->hydrateSimpleText($result, $platform);
    }

    private function checkResult(array $result): void
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

    private function hydrateTextArea(array $result, AbstractPlatform $platform): TextAttribute
    {
        $isRichTextEditor = $result['additional_properties']['is_rich_text_editor'];
        $maxLength = $this->getMaxLength($platform, $result['additional_properties']['max_length']);

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

    private function hydrateSimpleText($result, AbstractPlatform $platform): TextAttribute
    {
        $maxLength = $this->getMaxLength($platform, $result['additional_properties']['max_length']);
        $validationRule = $this->getValidationRule($platform, $result['additional_properties']['validation_rule']);
        $regularExpression = $this->getRegularExpression($platform, $result['additional_properties']['regular_expression']);

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

    private function getMaxLength(AbstractPlatform $platform, $maxLength): AttributeMaxLength
    {
        if (null === $maxLength) {
            return AttributeMaxLength::noLimit();
        }
        $maxLength = Type::getType(Type::INTEGER)->convertToPhpValue($maxLength, $platform);

        return AttributeMaxLength::fromInteger($maxLength);
    }

    private function getValidationRule(AbstractPlatform $platform, $validationRule): AttributeValidationRule
    {
        if (null === $validationRule) {
            return AttributeValidationRule::none();
        }
        $validationRule = Type::getType(Type::STRING)->convertToPhpValue($validationRule, $platform);

        return AttributeValidationRule::fromString($validationRule);
    }

    private function getRegularExpression(AbstractPlatform $platform, $regularExpression): AttributeRegularExpression
    {
        if (null === $regularExpression) {
            return AttributeRegularExpression::createEmpty();
        }
        $regularExpression = Type::getType(Type::STRING)->convertToPhpValue($regularExpression, $platform);

        return AttributeRegularExpression::fromString($regularExpression);
    }
}
