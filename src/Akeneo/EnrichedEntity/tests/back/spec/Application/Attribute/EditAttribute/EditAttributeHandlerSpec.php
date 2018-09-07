<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterRegistryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditAttributeHandlerSpec extends ObjectBehavior
{
    function let(
        AttributeUpdaterRegistryInterface $editAttributeAdapterRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($editAttributeAdapterRegistry, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeHandler::class);
    }

    function it_updates_an_attribute(
        $attributeRepository,
        $editAttributeAdapterRegistry,
        TextAttribute $attribute,
        AttributeUpdaterInterface $editRequiredAdapter,
        AttributeUpdaterInterface $editMaxFileSizeAdapter
    ) {
        $editAttributeCommand = $this->getEditCommand();
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString($editAttributeCommand->identifier))->willReturn($attribute);
        $editAttributeAdapterRegistry->getUpdater($attribute, Argument::type(EditIsRequiredCommand::class))->willReturn($editRequiredAdapter);
        $editAttributeAdapterRegistry->getUpdater($attribute, Argument::type(EditMaxFileSizeCommand::class))->willReturn($editMaxFileSizeAdapter);
        $editRequiredAdapter->__invoke($attribute, Argument::type(EditIsRequiredCommand::class))->willReturn($attribute);
        $editMaxFileSizeAdapter->__invoke($attribute, Argument::type(EditMaxFileSizeCommand::class))->willReturn($attribute);
        $attributeRepository->update($attribute)->shouldBeCalled();

        ($this)($editAttributeCommand);
    }

    private function getEditCommand(): EditAttributeCommand
    {
        $editRequiredAttribute = new EditIsRequiredCommand();
        $editRequiredAttribute->identifier = 'designer_name_fingerprint';
        $editRequiredAttribute->isRequired = true;

        $editMaxFileSize = new EditMaxFileSizeCommand();
        $editMaxFileSize->identifier = 'designer_name_fingerprint';
        $editMaxFileSize->maxFileSize = '154';

        $editAttributeCommand = new EditAttributeCommand();
        $editAttributeCommand->identifier = 'designer_name_fingerprint';
        $editAttributeCommand->editCommands = [$editRequiredAttribute, $editMaxFileSize];

        return $editAttributeCommand;
    }
}
