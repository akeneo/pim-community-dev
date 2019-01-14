<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\TextAttributeFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use PhpSpec\ObjectBehavior;

class TextAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TextAttributeFactory::class);
    }

    function it_only_supports_create_text_commands()
    {
        $this->supports(new CreateTextAttributeCommand())->shouldReturn(true);
        $this->supports(new CreateImageAttributeCommand())->shouldReturn(false);
    }

    function it_creates_a_simple_text_attribute_with_a_command()
    {
        $command = new CreateTextAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextarea = false;
        $command->validationRule = AttributeValidationRule::NONE;
        $command->regularExpression = AttributeRegularExpression::EMPTY;

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                 => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => 155,
            'is_textarea'               => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'none',
            'regular_expression'         => null,
        ]);
    }

    function it_creates_a_simple_text_attribute_having_no_validation_with_a_command()
    {
        $command = new CreateTextAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextarea = false;
        $command->validationRule = AttributeValidationRule::NONE;
        $command->regularExpression = AttributeRegularExpression::EMPTY;

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                 => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => 155,
            'is_textarea'               => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'none',
            'regular_expression'         => null,
        ]);
    }

    function it_creates_a_simple_text_attribute_with_no_max_length_limit()
    {
        $command = new CreateTextAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = AttributeMaxLength::NO_LIMIT;
        $command->validationRule = AttributeValidationRule::NONE;
        $command->regularExpression = AttributeRegularExpression::EMPTY;

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                 => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_textarea'               => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'none',
            'regular_expression'         => null,
        ]);
    }

    function it_creates_a_simple_text_attribute_with_a_regular_expression_validation()
    {
        $command = new CreateTextAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = AttributeMaxLength::NO_LIMIT;
        $command->isTextarea = false;
        $command->validationRule = AttributeValidationRule::REGULAR_EXPRESSION;
        $command->regularExpression = '/\w+/';

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                 => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_textarea'               => false,
            'is_rich_text_editor'        => false,
            'validation_rule'            => 'regular_expression',
            'regular_expression'         => '/\w+/',
        ]);
    }

    function it_creates_a_textarea_with_rich_editor()
    {
        $command = new CreateTextAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextarea = true;
        $command->isRichTextEditor = true;

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                 => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => 155,
            'is_textarea'               => true,
            'is_rich_text_editor'        => true,
            'validation_rule'            => 'none',
            'regular_expression'         => null,
        ]);
    }
}
