<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommandFactory;
use PhpSpec\ObjectBehavior;

class EditIsRequiredCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditIsRequiredCommandFactory::class);
    }

    function it_only_supports_attribute_property_required_edits()
    {
        $this->supports([
            'identifier' => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'is_required'   => true,
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
            'is_required'   => true,
        ]);
        $command->shouldBeAnInstanceOf(EditIsRequiredCommand::class);
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
