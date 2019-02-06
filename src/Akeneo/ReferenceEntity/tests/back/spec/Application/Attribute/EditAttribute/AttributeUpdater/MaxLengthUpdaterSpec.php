<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\MaxLengthUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class MaxLengthUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MaxLengthUpdater::class);
    }

    function it_only_supports_edit_max_length_command_for_text_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $maxLengthEditCommand = new EditMaxLengthCommand('name', 200);
        $labelEditCommand = new EditLabelsCommand('name', []);

        $this->supports($textAttribute, $maxLengthEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $maxLengthEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_max_length_property_of_a_text_attribute(TextAttribute $textAttribute)
    {
        $editMaxLength = new EditMaxLengthCommand('name',200);
        $textAttribute->setMaxLength(AttributeMaxLength::fromInteger(200))->shouldBeCalled();
        $this->__invoke($textAttribute, $editMaxLength)->shouldReturn($textAttribute);
    }

    function it_edits_sets_no_limit_to_the_max_length(TextAttribute $textAttribute)
    {
        $editMaxLength = new EditMaxLengthCommand('name', null);
        $textAttribute->setMaxLength(AttributeMaxLength::noLimit())->shouldBeCalled();
        $this->__invoke($textAttribute, $editMaxLength)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $rightAttribute, ImageAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $rightCommand = new EditMaxLengthCommand('name', null);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
