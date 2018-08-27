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
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextArea;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
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
            AttributeRegularExpression::none()
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
            AttributeRegularExpression::none()
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
            AttributeRegularExpression::none(),
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
            AttributeRegularExpression::none(),
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
            AttributeRegularExpression::none(),
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
                'validation_rule'            => null,
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
            AttributeRegularExpression::fromString('\w+-[0-9]')
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
                'validation_rule'            => 'regular_expression',
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
                'validation_rule'            => null,
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
                'validation_rule'            => null,
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
                'validation_rule'            => null,
                'regular_expression'         => null,
            ]
        );
    }

    function it_updates_the_validation_rule_of_a_simple_text_when_setting_the_is_text_area_flag_to_true()
    {
        $this->beConstructedThrough('createText', [
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
            AttributeRegularExpression::fromString('/\w+/')
        ]);
        $this->setIsTextArea(AttributeIsTextArea::fromBoolean(true));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(true);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeNull();
        $normalizedAttribute['regular_expression']->shouldBeNull();
    }

    function it_updates_the_optional_options_to_default_values_when_changing_the_is_text_area_flag()
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
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->setIsTextArea(AttributeIsTextArea::fromBoolean(false));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(false);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeNull();
        $normalizedAttribute['regular_expression']->shouldBeNull();
    }

    function it_does_not_update_optional_options_if_it_updates_the_is_text_area_flag_to_the_same_value()
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
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->setIsTextArea(AttributeIsTextArea::fromBoolean(true));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(true);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(true);
    }

    function it_updates_the_validation_rule_a_simple_text_attribute()
    {
        $this->beConstructedThrough('createText', [
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
            AttributeRegularExpression::none()
        ]);
        $this->setValidationRule(AttributeValidationRule::fromString(AttributeValidationRule::EMAIL));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo(AttributeValidationRule::EMAIL);
        $normalizedAttribute['regular_expression']->shouldBeEqualTo(AttributeValidationRule::NONE);
    }

    function it_sets_the_regular_expression_to_empty_if_the_validation_rule_is_not_regular_expression()
    {
        $this->beConstructedThrough('createText', [
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
            AttributeRegularExpression::fromString('/\w+/')
        ]);
        $this->setValidationRule(AttributeValidationRule::fromString(AttributeValidationRule::EMAIL));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo(AttributeValidationRule::EMAIL);
        $normalizedAttribute['regular_expression']->shouldBeEqualTo(AttributeRegularExpression::NONE);
    }

    function it_does_not_update_the_validation_rule_a_text_area_attribute()
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
            AttributeIsRichTextEditor::fromBoolean(false)
        ]);
        $this->shouldThrow(\LogicException::class)->duringSetValidationRule(AttributeValidationRule::fromString(AttributeValidationRule::EMAIL));
    }

    function it_updates_the_regular_expression_of_a_text_attribute()
    {
        $this->beConstructedThrough('createText', [
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
            AttributeRegularExpression::fromString('/[0-9]*/')
        ]);
        $this->setRegularExpression(AttributeRegularExpression::fromString('/\w+/'));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo(AttributeValidationRule::REGULAR_EXPRESSION);
        $normalizedAttribute['regular_expression']->shouldBeEqualTo('/\w+/');
    }

    function it_does_not_update_the_regular_expression_if_the_is_text_area_flag_is_true()
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
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->shouldThrow(\LogicException::class)->duringSetRegularExpression(AttributeRegularExpression::fromString('/\w+/'));
    }

    function it_does_not_update_the_regular_expression_if_the_validation_rule_is_not_set_to_regular_expression()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::none()
        ]);
        $this->shouldThrow(\LogicException::class)->duringSetRegularExpression(AttributeRegularExpression::fromString('/\w+/'));
    }

    function it_tells_if_it_is_a_text_area()
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
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->isTextArea()->shouldBeEqualTo(true);
    }

    function it_updates_the_is_rich_text_editor_flag_of_a_text_area_attribute()
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
            AttributeIsRichTextEditor::fromBoolean(false)
        ]);
        $this->setIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(true));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_text_area']->shouldBeEqualTo(true);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(true);
    }

    function it_does_not_update_the_is_rich_text_editor_flag_if_the_text_area_flag_is_false()
    {
        $this->beConstructedThrough('createText', [
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
            AttributeRegularExpression::none()
        ]);
        $this->shouldThrow(\LogicException::class)->duringSetIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(true));
    }
}
