<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsReadOnlyCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsReadOnlyCommandFactory;
use PhpSpec\ObjectBehavior;

class EditIsReadOnlyCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditIsReadOnlyCommandFactory::class);
    }

    function it_only_supports_attribute_property_read_only_edits()
    {
        $this->supports([
            'identifier' => ['identifier' => 'name', 'asset_family_identifier' => 'designer'],
            'is_read_only' => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => ['identifier' => 'name', 'asset_family_identifier' => 'designer'],
            'is_read_only' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => ['identifier' => 'name', 'asset_family_identifier' => 'designer'],
            'labels' => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_read_only_property_of_an_attribute()
    {
        $command = $this->create([
            'identifier' => 'name',
            'is_read_only' => true,
        ]);
        $command->shouldBeAnInstanceOf(EditIsReadOnlyCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->isReadOnly->shouldBeEqualTo(true);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => [
                        'identifier'                 => 'portrait',
                        'asset_family_identifier' => 'designer',
                    ],
                    'wrong_property' => 10,
                ],
            ]);
    }
}
