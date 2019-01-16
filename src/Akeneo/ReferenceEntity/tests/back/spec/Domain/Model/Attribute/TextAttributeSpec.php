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

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class TextAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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

    function it_can_create_a_textarea_with_rich_text_editor()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }

    function it_can_create_a_simple_text_with_a_validation_rule_on_regex_with_a_regex()
    {
        $this::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );
    }

    function it_cannot_create_a_simple_text_with_validation_regex_without_specifying_a_regex()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::createEmpty(),
        ]);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_normalizes_a_textarea()
    {
        $this->normalize()->shouldReturn([
                'identifier'                 => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_textarea'               => true,
                'is_rich_text_editor'        => false,
                'validation_rule'            => 'none',
                'regular_expression'         => null,
            ]
        );
    }

    function it_normalizes_a_simple_text_with_a_validation_rule_and_regex()
    {
        $this::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+-[0-9]/')
        )->normalize()->shouldReturn([
                'identifier'                 => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_textarea'                => false,
                'is_rich_text_editor'        => false,
                'validation_rule'            => 'regular_expression',
                'regular_expression'         => '/\w+-[0-9]/',
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
                'identifier'                 => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Désignation', 'en_US' => 'Name', 'de_DE' => 'Bezeichnung'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_textarea'               => true,
                'is_rich_text_editor'        => false,
                'validation_rule'            => 'none',
                'regular_expression'         => null,
            ]
        );
    }

    function it_updates_its_max_length()
    {
        $this->setMaxLength(AttributeMaxLength::fromInteger(100));
        $this->normalize()->shouldBe([
                'identifier'                 => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => true,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 100,
                'is_textarea'               => true,
                'is_rich_text_editor'        => false,
                'validation_rule'            => 'none',
                'regular_expression'         => null,
            ]
        );
    }

    function it_updates_is_required_and_returns_a_new_instance_of_itself()
    {
        $this->setIsRequired(AttributeIsRequired::fromBoolean(false));
        $this->normalize()->shouldBe([
                'identifier'                 => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => ['fr_FR' => 'Nom', 'en_US' => 'Name'],
                'order'                      => 0,
                'is_required'                => false,
                'value_per_channel'          => true,
                'value_per_locale'           => true,
                'type'                       => 'text',
                'max_length'                 => 300,
                'is_textarea'               => true,
                'is_rich_text_editor'        => false,
                'validation_rule'            => 'none',
                'regular_expression'         => null,
            ]
        );
    }

    function it_updates_the_validation_rule_of_a_simple_text_when_setting_the_is_textarea_flag_to_true()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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
        $this->setIsTextarea(AttributeIsTextarea::fromBoolean(true));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(true);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo('none');
        $normalizedAttribute['regular_expression']->shouldBeNull();
    }

    function it_updates_the_optional_options_to_default_values_when_changing_the_is_textarea_flag()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->setIsTextarea(AttributeIsTextarea::fromBoolean(false));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(false);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo('none');
        $normalizedAttribute['regular_expression']->shouldBeNull();
    }

    function it_does_not_update_optional_options_if_it_updates_the_is_textarea_flag_to_the_same_value()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->setIsTextarea(AttributeIsTextarea::fromBoolean(true));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(true);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(true);
    }

    function it_updates_the_validation_rule_a_simple_text_attribute()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ]);
        $this->setValidationRule(AttributeValidationRule::fromString(AttributeValidationRule::EMAIL));
        $normalizedAttribute = $this->normalize();
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo(AttributeValidationRule::EMAIL);
        $normalizedAttribute['regular_expression']->shouldBeEqualTo(AttributeRegularExpression::EMPTY);
    }

    function it_sets_the_regular_expression_to_empty_if_the_validation_rule_is_not_regular_expression()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo(AttributeValidationRule::EMAIL);
        $normalizedAttribute['regular_expression']->shouldBeEqualTo(AttributeRegularExpression::EMPTY);
    }

    function it_does_not_update_the_validation_rule_a_textarea_attribute_if_the_validation_rule_is_not_none()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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

    function it_updates_the_validation_rule_of_a_textarea_attribute_if_the_validation_rule_is_none()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(false)
        ]);
        $this->setValidationRule(AttributeValidationRule::none());
        $this->normalize()['validation_rule']->shouldBeEqualTo('none');
    }

    function it_updates_the_regular_expression_of_a_textarea_attribute_if_the_regular_expression_is_empty()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(false)
        ]);
        $this->setRegularExpression(AttributeRegularExpression::createEmpty());
        $this->normalize()['regular_expression']->shouldBeEqualTo(null);
    }

    function it_updates_the_regular_expression_of_a_text_attribute()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(false);
        $normalizedAttribute['validation_rule']->shouldBeEqualTo(AttributeValidationRule::REGULAR_EXPRESSION);
        $normalizedAttribute['regular_expression']->shouldBeEqualTo('/\w+/');
    }

    function it_does_not_update_the_regular_expression_if_the_is_textarea_flag_is_true()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::createEmpty()
        ]);
        $this->shouldThrow(\LogicException::class)->duringSetRegularExpression(AttributeRegularExpression::fromString('/\w+/'));
    }

    function it_tells_if_it_is_a_textarea()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeIsRichTextEditor::fromBoolean(true)
        ]);
        $this->isTextarea()->shouldBeEqualTo(true);
    }

    function it_updates_the_is_rich_text_editor_flag_of_a_textarea_attribute()
    {
        $this->beConstructedThrough('createTextarea', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
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
        $normalizedAttribute['is_textarea']->shouldBeEqualTo(true);
        $normalizedAttribute['is_rich_text_editor']->shouldBeEqualTo(true);
    }

    function it_does_not_update_the_is_rich_text_editor_flag_if_the_textarea_flag_is_false()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ]);
        $this->shouldThrow(\LogicException::class)->duringSetIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(true));
    }

    function it_does_updates_the_is_rich_text_editor_flag_if_the_textarea_flag_is_false()
    {
        $this->beConstructedThrough('createText', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ]);
        $this->setIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(false));
        $this->normalize()['is_rich_text_editor']->shouldBeEqualTo(false);
    }

    function it_tells_if_it_has_a_value_per_channel()
    {
        $this->hasValuePerChannel()->shouldReturn(true);
    }

    function it_tells_if_it_has_a_value_per_locale()
    {
        $this->hasValuePerLocale()->shouldReturn(true);
    }
}
