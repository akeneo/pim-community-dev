<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsDecimalCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsDecimalCommandFactory;
use PhpSpec\ObjectBehavior;

class EditIsDecimalCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditIsDecimalCommandFactory::class);
    }

    function it_only_supports_attribute_property_is_decimal_edits()
    {
        $this->supports([
            'identifier'   => 'name',
            'is_decimal' => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier'   => 'name',
            'is_decimal' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_is_decimal_flag()
    {
        $command = $this->create([
            'identifier' => 'name',
            'is_decimal'   => true,
        ]);
        $command->shouldBeAnInstanceOf(EditIsDecimalCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->isDecimal->shouldBeEqualTo(true);
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
