<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegex;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class TextAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('createTextArea', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(false),
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextAttribute::class);
    }

    function it_can_create_a_text_area_with_rich_text_editor()
    {
        $this->beConstructedThrough('createTextArea', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(true),
        ]);
    }

    function it_can_create_simple_texts()
    {
        $this::createText(
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegex::none()
        );
    }

    function it_can_create_a_simple_text_with_a_validation_rule_on_regex_with_a_regex()
    {
        $this::createText(
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegex::none()
        );
    }

    function it_cannot_have_an_enriched_entity_identifier_different_from_the_composite_key()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createText', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('different_enriched_entity_code'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegex::none()
        ]);
    }

    function it_cannot_have_a_code_different_from_the_composite_key()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createText', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('different_attribute_code'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegex::none()
        ]);
    }

    function it_cannot_create_a_simple_text_with_validation_regex_without_specifying_a_regex()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createText', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegex::none()
        ]);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_normalizes_a_text_area()
    {
        $this->normalize()->shouldReturn([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'name',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_text_area'               => true,
                'is_rich_text_editor'        => false,
                'valdiation_rule'            => null,
                'regular_expression'         => null,
            ]
        );
    }

    function it_normalizes_a_simple_text_with_a_validation_rule_and_regex()
    {
        $this::createText(
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegex::fromString('\w+-[0-9]')
        )->normalize()->shouldReturn([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'name',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_text_area'               => false,
                'is_rich_text_editor'        => false,
                'valdiation_rule'            => 'regular_expression',
                'regular_expression'         => '\w+-[0-9]',
            ]
        );
    }

    function it_updates_its_label_and_returns_a_new_instance_of_itself()
    {
        $this->updateLabels(LabelCollection::fromArray([
            'fr_FR' => 'Désignation',
            'de_DE' => 'Bezeichnung',
        ]));
        $this->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'name',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Désignation', 'de_DE' => 'Bezeichnung'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_text_area'               => true,
                'is_rich_text_editor'        => false,
                'valdiation_rule'            => null,
                'regular_expression'         => null,
            ]
        );
    }

    function it_updates_its_max_length()
    {
        $this->setMaxLength(AttributeMaxLength::fromInteger(100));
        $this->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'name',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 100,
                'is_text_area'               => true,
                'is_rich_text_editor'        => false,
                'valdiation_rule'            => null,
                'regular_expression'         => null,
            ]
        );
    }

    function it_updates_is_required_and_returns_a_new_instance_of_itself()
    {
        $this->setIsRequired(AttributeIsRequired::fromBoolean(false));
        $this->normalize()->shouldBe([
                'identifier'                 => [
                    'enriched_entity_identifier' => 'designer',
                    'identifier'                 => 'name',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => false,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_text_area'               => true,
                'is_rich_text_editor'        => false,
                'valdiation_rule'            => null,
                'regular_expression'         => null,
            ]
        );
    }
}
