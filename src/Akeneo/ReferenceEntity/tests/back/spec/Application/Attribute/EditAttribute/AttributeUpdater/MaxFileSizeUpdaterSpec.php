<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\MaxFileSizeUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class MaxFileSizeUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MaxFileSizeUpdater::class);
    }

    function it_only_supports_edit_max_file_size_command_of_image_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $maxFileSizeEditCommand = new EditMaxFileSizeCommand('name', '120');
        $labelEditCommand = new EditLabelsCommand('name', []);

        $this->supports($imageAttribute, $maxFileSizeEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $labelEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $maxFileSizeEditCommand)->shouldReturn(false);
    }

    function it_edits_the_max_file_size_property_of_an_image_attribute(ImageAttribute $imageAttribute)
    {
        $editMaxFileSize = new EditMaxFileSizeCommand('name', '151.1');
        $editMaxFileSize->maxFileSize = '151.1';
        $imageAttribute->setMaxFileSize(AttributeMaxFileSize::fromString('151.1'))->shouldBeCalled();
        $this->__invoke($imageAttribute, $editMaxFileSize)->shouldReturn($imageAttribute);
    }

    function it_edits_sets_the_max_file_to_no_limit(ImageAttribute $textAttribute)
    {
        $editMaxFileSize = new EditMaxFileSizeCommand('name', null);
        $textAttribute->setMaxFileSize(AttributeMaxFileSize::noLimit())->shouldBeCalled();
        $this->__invoke($textAttribute, $editMaxFileSize)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(ImageAttribute $rightAttribute, TextAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $rightCommand = new EditMaxFileSizeCommand('name', '120');
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
