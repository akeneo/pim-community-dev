<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommandFactory;
use PhpSpec\ObjectBehavior;

class EditLabelsCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditLabelsCommandFactory::class);
    }

    function it_only_supports_attribute_labels_edits()
    {
        $this->supports([
            'identifier' => 'portrait',
            'labels'     => ['fr_FR' => 'Nickname'],
        ])->shouldReturn(true);
        $this->supports([
            'identifier' => 'portrait',
            'labels'     => null
        ])->shouldReturn(true);
        $this->supports([
            'identifier'    => 'portrait',
            'max_file_size' => '172.50',
        ])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_command_to_edit_labels_of_an_attribute()
    {
        $command = $this->create([
            'identifier' => 'portrait',
            'labels'     => ['fr_FR' => 'Nickname'],
        ]);
        $command->shouldBeAnInstanceOf(EditLabelsCommand::class);
        $command->identifier->shouldBeEqualTo('portrait');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Nickname']);
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
