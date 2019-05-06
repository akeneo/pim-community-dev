<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxValueCommandFactory;
use PhpSpec\ObjectBehavior;

class EditMaxValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditMaxValueCommandFactory::class);
    }

    function it_only_supports_attribute_property_min_value_edits()
    {
        $this->supports(['identifier' => 'number', 'max_value' => '172.50'])->shouldReturn(true);
        $this->supports(['identifier' => 'number', 'max_value' => null])->shouldReturn(true);
        $this->supports(['identifier' => 'number', 'labels' => 'wrong_property',])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_min_value_property_of_an_attribute()
    {
        $command = $this->create(['identifier' => 'number', 'max_value' => '172']);

        $command->shouldBeAnInstanceOf(EditMaxCommand::class);
        $command->identifier->shouldBeEqualTo('number');
        $command->maxValue->shouldBeEqualTo('172');
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)->during(
            'create',
            [['identifier' => 'number', 'wrong_property' => 10]]
        );
    }
}
