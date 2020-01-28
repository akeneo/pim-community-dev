<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\AllowedExtensionsUpdater;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAllowedExtensionsCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class AllowedExtensionsUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AllowedExtensionsUpdater::class);
    }

    function it_only_supports_edit_allowed_extensions_command_of_media_file_attributes(
        TextAttribute $textAttribute,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $allowedExtensionsEditCommand = new EditAllowedExtensionsCommand('image', []);
        $labelEditCommand = new EditLabelsCommand('name', []);

        $this->supports($mediaFileAttribute, $allowedExtensionsEditCommand)->shouldReturn(true);
        $this->supports($mediaFileAttribute, $labelEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $allowedExtensionsEditCommand)->shouldReturn(false);
    }

    function it_edits_the_allowed_extensions_property_of_a_media_file_attribute(MediaFileAttribute $mediaFileAttribute)
    {
        $editAllowedExtensions = new EditAllowedExtensionsCommand('image', ['png']);
        $mediaFileAttribute->setAllowedExtensions(AttributeAllowedExtensions::fromList(['png']))->shouldBeCalled();
        $this->__invoke($mediaFileAttribute, $editAllowedExtensions)->shouldReturn($mediaFileAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(MediaFileAttribute $rightAttribute, TextAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $rightCommand = new EditAllowedExtensionsCommand('image', ['png']);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
