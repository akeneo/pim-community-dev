<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextAreaCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextAreaCommandFactory;
use PhpSpec\ObjectBehavior;

class EditIsTextAreaCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditIsTextAreaCommandFactory::class);
    }

    function it_only_supports_attribute_property_is_text_area_edits()
    {
        $this->supports([
            'identifier'          => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'is_text_area' => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'],
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_is_text_area_flag()
    {
        $command = $this->create([
            'identifier' => [
                'identifier'                 => 'name',
                'enriched_entity_identifier' => 'designer',
            ],
            'is_text_area'   => true,
        ]);
        $command->shouldBeAnInstanceOf(EditIsTextAreaCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => 'name',
            'enriched_entity_identifier' => 'designer',
        ]);
        $command->isTextArea->shouldBeEqualTo(true);
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
