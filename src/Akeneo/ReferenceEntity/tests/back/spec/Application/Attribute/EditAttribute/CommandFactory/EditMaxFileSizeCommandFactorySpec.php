<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommandFactory;
use PhpSpec\ObjectBehavior;

class EditMaxFileSizeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditMaxFileSizeCommandFactory::class);
    }

    function it_only_supports_attribute_property_max_file_size_edits()
    {
        $this->supports([
            'identifier'    => 'portrait',
            'max_file_size' => '172.50',
        ])->shouldReturn(true);
        $this->supports([
            'identifier'    => 'portrait',
            'max_file_size' => null
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'portrait',
            'labels'     => 'wrong_property',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_the_max_file_size_property_of_an_attribute()
    {
        $command = $this->create([
            'identifier'    => 'portrait',
            'max_file_size' => '172.50',
        ]);
        $command->shouldBeAnInstanceOf(EditMaxFileSizeCommand::class);
        $command->identifier->shouldBeEqualTo('portrait');
        $command->maxFileSize->shouldBeEqualTo('172.50');
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
