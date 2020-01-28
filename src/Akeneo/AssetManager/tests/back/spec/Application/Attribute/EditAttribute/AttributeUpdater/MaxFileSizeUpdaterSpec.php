<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\MaxFileSizeUpdater;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class MaxFileSizeUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MaxFileSizeUpdater::class);
    }

    function it_only_supports_edit_max_file_size_command_of_media_file_attributes(
        TextAttribute $textAttribute,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $maxFileSizeEditCommand = new EditMaxFileSizeCommand('name', '120');
        $labelEditCommand = new EditLabelsCommand('name', []);

        $this->supports($mediaFileAttribute, $maxFileSizeEditCommand)->shouldReturn(true);
        $this->supports($mediaFileAttribute, $labelEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $maxFileSizeEditCommand)->shouldReturn(false);
    }

    function it_edits_the_max_file_size_property_of_a_media_file_attribute(MediaFileAttribute $mediaFileAttribute)
    {
        $editMaxFileSize = new EditMaxFileSizeCommand('name', '151.1');
        $editMaxFileSize->maxFileSize = '151.1';
        $mediaFileAttribute->setMaxFileSize(AttributeMaxFileSize::fromString('151.1'))->shouldBeCalled();
        $this->__invoke($mediaFileAttribute, $editMaxFileSize)->shouldReturn($mediaFileAttribute);
    }

    function it_edits_sets_the_max_file_to_no_limit(MediaFileAttribute $textAttribute)
    {
        $editMaxFileSize = new EditMaxFileSizeCommand('name', null);
        $textAttribute->setMaxFileSize(AttributeMaxFileSize::noLimit())->shouldBeCalled();
        $this->__invoke($textAttribute, $editMaxFileSize)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(MediaFileAttribute $rightAttribute, TextAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $rightCommand = new EditMaxFileSizeCommand('name', '120');
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
