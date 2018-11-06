<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditOptionsCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditOptionsCommandFactory;
use PhpSpec\ObjectBehavior;

class EditOptionsCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditOptionsCommandFactory::class);
    }

    function it_only_supports_attribute_option_edits()
    {
        $this->supports(['identifier' => ['some_identifier'], 'options' => 'some_options'])->shouldReturn(true);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_options_of_an_attribute()
    {
        $command = $this->create([
            'identifier' => [
                'identifier'                 => 'favorite_color',
                'reference_entity_identifier' => 'designer',
            ],
            'options' => ['some_options'],
        ]);
        $command->shouldBeAnInstanceOf(EditOptionsCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => 'favorite_color',
            'reference_entity_identifier' => 'designer',
        ]);
        $command->options->shouldBeEqualTo(['some_options']);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                    [
                        'identifier'     => [
                            'identifier'                  => 'portrait',
                            'reference_entity_identifier' => 'designer',
                        ],
                        'wrong_property' => 10,
                    ],
                ]
            );
    }
}
