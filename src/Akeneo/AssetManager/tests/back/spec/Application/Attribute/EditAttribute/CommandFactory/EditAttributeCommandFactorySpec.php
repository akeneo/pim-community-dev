<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactory;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use PhpSpec\ObjectBehavior;

class EditAttributeCommandFactorySpec extends ObjectBehavior
{
    function let(EditAttributeCommandFactoryRegistryInterface $editAttributeCommandFactoryRegistry)
    {
        $this->beConstructedWith($editAttributeCommandFactoryRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeCommandFactory::class);
    }

    function it_supports_attribute_edits()
    {
        $this->supports(['identifier' => ['identifier' => 'portrait', 'reference_entity_identifier' => 'designer']])
            ->shouldReturn(true);
        $this->supports(['max_file_size' => '172.50'])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_an_edit_attribute_command_by_recursively_calling_other_edit_attribute_property_factories(
       $editAttributeCommandFactoryRegistry,
        EditAttributeCommandFactoryInterface $editMaxFileSizeCommandFactory,
        EditAttributeCommandFactoryInterface $editLabelsCommandFactory
    ) {
        $normalizedCommand = [
            'identifier'    => 'portrait',
            'labels' => ['fr_FR' => 'Image autobiographique'],
            'max_file_size' => '172.50'
        ];
        $editAttributeCommandFactoryRegistry->getFactories($normalizedCommand)->willReturn([$editMaxFileSizeCommandFactory, $editLabelsCommandFactory]);
        $editMaxFileSizeCommand = new EditMaxFileSizeCommand($normalizedCommand['identifier'], $normalizedCommand['max_file_size']);
        $editLabelsCommand = new EditLabelsCommand($normalizedCommand['identifier'], $normalizedCommand['labels']);

        $editMaxFileSizeCommandFactory->create($normalizedCommand)->willReturn($editMaxFileSizeCommand);
        $editLabelsCommandFactory->create($normalizedCommand)->willReturn($editLabelsCommand);

        $command = $this->create($normalizedCommand);
        $command->shouldBeAnInstanceOf(EditAttributeCommand::class);
        $command->identifier->shouldBeEqualTo('portrait');
        $command->editCommands->shouldBeEqualTo([$editMaxFileSizeCommand, $editLabelsCommand]);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)->during('create', [['wrong_property' => 10]]);
    }
}
