<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextareaCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextareaCommandFactory;
use PhpSpec\ObjectBehavior;

class EditIsTextareaCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditIsTextareaCommandFactory::class);
    }

    function it_only_supports_attribute_property_is_textarea_edits()
    {
        $this->supports([
            'identifier'   => 'name',
            'is_textarea' => true,
        ])->shouldReturn(true);
        $this->supports([
            'identifier'   => 'name',
            'is_textarea' => null,
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'name',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_is_textarea_flag()
    {
        $command = $this->create([
            'identifier' => 'name',
            'is_textarea'   => true,
        ]);
        $command->shouldBeAnInstanceOf(EditIsTextareaCommand::class);
        $command->identifier->shouldBeEqualTo('name');
        $command->isTextarea->shouldBeEqualTo(true);
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
