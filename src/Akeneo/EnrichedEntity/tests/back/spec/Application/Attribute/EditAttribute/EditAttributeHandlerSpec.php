<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeUpdater\EditAttributeUpdaterInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeUpdater\EditAttributeUpdaterRegistryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditAttributeHandlerSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeUpdaterRegistryInterface $editAttributeAdapterRegistry
    ) {
        $this->beConstructedWith($attributeRepository, $editAttributeAdapterRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeHandler::class);
    }

    function it_updates_an_attribute(
        $attributeRepository,
        $editAttributeAdapterRegistry,
        TextAttribute $attribute,
        EditAttributeUpdaterInterface $editRequiredAdapter,
        EditAttributeUpdaterInterface $editMaxFileSizeAdapter
    ) {
        $editAttributeCommand = $this->getEditCommand();
        $attributeRepository->getByIdentifier(AttributeIdentifier::create('designer', 'name'))->willReturn($attribute);
        $editAttributeAdapterRegistry->getAdapter(Argument::type(EditRequiredCommand::class))->willReturn($editRequiredAdapter);
        $editAttributeAdapterRegistry->getAdapter(Argument::type(EditMaxFileSizeCommand::class))->willReturn($editMaxFileSizeAdapter);
        $editRequiredAdapter->__invoke($attribute, Argument::type(EditRequiredCommand::class))->willReturn($attribute);
        $editMaxFileSizeAdapter->__invoke($attribute, Argument::type(EditMaxFileSizeCommand::class))->willReturn($attribute);
        $attributeRepository->update($attribute)->shouldBeCalled();

        ($this)($editAttributeCommand);
    }

    private function getEditCommand(): EditAttributeCommand
    {
        $editRequiredAttribute = new EditRequiredCommand();
        $editRequiredAttribute->identifier = ['enriched_entity_identifier' => 'designer', 'identifier' => 'name'];
        $editRequiredAttribute->required = true;

        $editMaxFileSize = new EditMaxFileSizeCommand();
        $editMaxFileSize->identifier = ['enriched_entity_identifier' => 'designer', 'identifier' => 'name'];
        $editMaxFileSize->maxFileSize = '154';

        $editAttributeCommand = new EditAttributeCommand();
        $editAttributeCommand->identifier = ['enriched_entity_identifier' => 'designer', 'identifier' => 'name'];
        $editAttributeCommand->editCommands = [$editRequiredAttribute, $editMaxFileSize];

        return $editAttributeCommand;
    }
}
