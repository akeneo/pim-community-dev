<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditDecimalsAllowedCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditDecimalsAllowedCommandFactory;
use PhpSpec\ObjectBehavior;

class EditDecimalsAllowedCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditDecimalsAllowedCommandFactory::class);
    }

    function it_only_supports_attribute_property_decimals_allowed_edits()
    {
        $this->supports([
            'identifier'   => 'name',
            'decimals_allowed' => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier'   => 'name',
            'decimals_allowed' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_decimals_allowed_flag()
    {
        $command = $this->create([
            'identifier' => 'name',
            'decimals_allowed' => true,
        ]);
        $command->shouldBeAnInstanceOf(EditDecimalsAllowedCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->decimalsAllowed->shouldBeEqualTo(true);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [
                [
                    'identifier'     => 'portrait',
                    'wrong_property' => 10,
                ],
            ]);
    }
}
