<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactory;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryRegistryInterface;
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
        $this->supports(['identifier' => ['identifier' => 'portrait', 'enriched_entity_identifier' => 'designer']])
            ->shouldReturn(true);
        $this->supports(['max_file_size' => '172.50'])->shouldReturn(false);
        $this->supports(['dummy' => 10])->shouldReturn(false);
    }

    function it_creates_a_edit_attribute_command_by_recursively_calling_other_edit_attribute_property_factories(
       $editAttributeCommandFactoryRegistry,
        EditAttributeCommandFactoryInterface $editMaxFileSizeCommandFactory,
        EditAttributeCommandFactoryInterface $editLabelsCommandFactory
    ) {
        $normalizedCommand = [
            'identifier'    => ['identifier' => 'portrait', 'enriched_entity_identifier' => 'designer'],
            'labels' => ['fr_FR' => 'Image autobiographique'],
            'max_file_size' => '172.50'
        ];
        $editAttributeCommandFactoryRegistry->getFactories($normalizedCommand)
            ->willReturn([$editMaxFileSizeCommandFactory, $editLabelsCommandFactory]);

        $command = $this->create($normalizedCommand);
        $command->shouldBeAnInstanceOf(EditAttributeCommand::class);
        $command->identifier->shouldBeEqualTo(['identifier' => 'portrait', 'enriched_entity_identifier' => 'designer']);
        $command->editCommands->shouldBeEqualTo([$editMaxFileSizeCommandFactory, $editLabelsCommandFactory]);
    }

    function it_throws_if_it_cannot_create_the_command()
    {
        $this->shouldThrow(\RuntimeException::class)->during('create', [['wrong_property' => 10]]);
    }
}
