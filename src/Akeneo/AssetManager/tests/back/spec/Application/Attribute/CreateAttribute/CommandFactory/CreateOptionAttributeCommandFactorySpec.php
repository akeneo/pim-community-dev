<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateOptionAttributeCommandFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateOptionAttributeCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateOptionAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateOptionAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_option()
    {
        $this->supports(['type' => 'option'])->shouldReturn(true);
        $this->supports(['type' => 'text'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_an_option_attribute()
    {
        $command = $this->create([
            'reference_entity_identifier' => 'designer',
            'code' => 'picture',
            'labels' => ['fr_FR' => 'Portrait'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false
        ]);

        $command->shouldBeAnInstanceOf(CreateOptionAttributeCommand::class);
        $command->referenceEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('picture');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Portrait']);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'reference_entity_identifier' => 'designer',
            'code' => 'picture',
            'is_required' => false,
            //'value_per_channel' => false, // For the test purpose, this one is missing
            'value_per_locale' => false,
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [$command]);
    }
}
