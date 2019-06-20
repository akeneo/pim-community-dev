<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateTextAttributeCommandFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateTextAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateTextAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_text()
    {
        $this->supports(['type' => 'text'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_text_attribute()
    {
        $command = $this->create([
            'reference_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'max_length' => 255,
            'is_textarea' => true,
            'is_rich_text_editor' => true,
            'validation_rule' => 'regular_expression',
            'regular_expression' => '/\w+/',
        ]);

        $command->shouldBeAnInstanceOf(CreateTextAttributeCommand::class);
        $command->referenceEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('name');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Nom']);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->maxLength->shouldBeEqualTo(255);
        $command->isTextarea->shouldBeEqualTo(true);
        $command->isRichTextEditor->shouldBeEqualTo(true);
        $command->validationRule->shouldBeEqualTo('regular_expression');
        $command->regularExpression->shouldBeEqualTo('/\w+/');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'reference_entity_identifier' => 'designer',
            // 'code' => 'name', // For the test purpose, this one is missing
            'labels' => ['fr_FR' => 'Nom'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'max_length' => 255,
            'is_textarea' => true,
            'is_rich_text_editor' => true,
            'validation_rule' => 'regular_expression',
            'regular_expression' => '/\w+/',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_creates_a_command_with_a_default_property_if_the_none_is_provided()
    {
        $command = $this->create([
            'reference_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'value_per_channel' => false,
            'value_per_locale' => false,
        ]);

        $command->shouldBeAnInstanceOf(CreateTextAttributeCommand::class);
        $command->isRequired->shouldBeEqualTo(false);
        $command->maxLength->shouldBeEqualTo(null);
        $command->isTextarea->shouldBeEqualTo(false);
        $command->isRichTextEditor->shouldBeEqualTo(false);
        $command->validationRule->shouldBeEqualTo('none');
        $command->regularExpression->shouldBeEqualTo(null);
    }
}
