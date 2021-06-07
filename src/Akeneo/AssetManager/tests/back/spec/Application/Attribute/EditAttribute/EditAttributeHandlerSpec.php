<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterInterface;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterRegistryInterface;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
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
        $editRequiredAttribute = new EditIsRequiredCommand(
            'designer_name_fingerprint',
            true
        );

        $editMaxFileSize = new EditMaxFileSizeCommand(
            'designer_name_fingerprint',
            '154'
        );

        return new EditAttributeCommand(
            'designer_name_fingerprint',
            [
                $editRequiredAttribute,
                $editMaxFileSize
            ]
        );
    }
}
