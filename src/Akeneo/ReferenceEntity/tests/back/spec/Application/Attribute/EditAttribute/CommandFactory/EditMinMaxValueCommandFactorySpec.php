<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMinMaxValueCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMinMaxValueCommandFactory;
use PhpSpec\ObjectBehavior;

class EditMinMaxValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditMinMaxValueCommandFactory::class);
    }

    function it_only_supports_attribute_property_min_max_value_edits()
    {
        $this->supports(['identifier' => 'number', 'min_value' => '172.50', 'max_value' => '0.5'])->shouldReturn(true);
        $this->supports(['identifier' => 'number', 'min_value' => null, 'max_value' => null])->shouldReturn(true);
        $this->supports(['identifier' => 'number', 'labels' => 'wrong_property',])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_min_value_property_of_an_attribute()
    {
        $command = $this->create(['identifier' => 'number', 'min_value' => '172', 'max_value' => '172']);

        $command->shouldBeAnInstanceOf(EditMinMaxValueCommand::class);
        $command->identifier->shouldBeEqualTo('number');
        $command->minValue->shouldBeEqualTo('172');
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)->during(
            'create',
            [['identifier' => 'number', 'wrong_property' => 10]]
        );
    }
}
