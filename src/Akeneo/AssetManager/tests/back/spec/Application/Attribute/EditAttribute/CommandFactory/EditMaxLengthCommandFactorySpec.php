<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommandFactory;
use PhpSpec\ObjectBehavior;

class EditMaxLengthCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditMaxLengthCommandFactory::class);
    }

    function it_only_supports_attribute_property_max_length_edits()
    {
        $this->supports([
            'identifier' => 'name',
            'max_length' => '154',
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'max_length' => null
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_max_length_of_an_attribute()
    {
        $command = $this->create([
            'identifier' => 'name',
            'max_length' => 100,
        ]);
        $command->shouldBeAnInstanceOf(EditMaxLengthCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->maxLength->shouldBeEqualTo(100);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => [
                        'identifier'                 => 'portrait',
                        'reference_entity_identifier' => 'designer',
                    ],
                    'wrong_property' => 10,
                ],
            ]);
    }
}
