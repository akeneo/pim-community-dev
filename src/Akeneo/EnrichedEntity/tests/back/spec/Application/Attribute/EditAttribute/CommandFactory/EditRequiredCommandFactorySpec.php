<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRequiredCommandFactory;
use PhpSpec\ObjectBehavior;

class EditRequiredCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditRequiredCommandFactory::class);
    }

    function it_only_supports_attribute_property_required_edits()
    {
        $this->supports([
            'identifier' => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'required'   => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_required_property_of_an_attribute()
    {
        $command = $this->create([
            'identifier' => [
                'identifier'                 => 'name',
                'enriched_entity_identifier' => 'designer',
            ],
            'required'   => true,
        ]);
        $command->shouldBeAnInstanceOf(EditRequiredCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => 'name',
            'enriched_entity_identifier' => 'designer',
        ]);
        $command->required->shouldBeEqualTo(true);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => [
                        'identifier'                 => 'portrait',
                        'enriched_entity_identifier' => 'designer',
                    ],
                    'wrong_property' => 10,
                ],
            ]);
    }
}
