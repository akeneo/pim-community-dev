<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\MaxFileSizeUpdater;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
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
        $maxFileSizeEditCommand = new EditMaxFileSizeCommand();
        $labelEditCommand = new EditLabelsCommand();

        $this->supports($imageAttribute, $maxFileSizeEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $labelEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $maxFileSizeEditCommand)->shouldReturn(false);
    }

    function it_edits_the_max_file_size_property_of_an_image_attribute(ImageAttribute $imageAttribute)
    {
        $editMaxFileSize = new EditMaxFileSizeCommand();
        $editMaxFileSize->maxFileSize = '151.1';
        $imageAttribute->setMaxFileSize(AttributeMaxFileSize::fromString('151.1'))->willReturn($imageAttribute);
        $this->__invoke($imageAttribute, $editMaxFileSize);
    }

    function it_throws_if_it_cannot_update_the_attribute(ImageAttribute $rightAttribute, TextAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand();
        $rightCommand = new EditMaxFileSizeCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
