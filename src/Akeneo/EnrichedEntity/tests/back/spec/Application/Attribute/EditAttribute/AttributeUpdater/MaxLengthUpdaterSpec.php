<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\MaxLengthUpdater;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
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
        $maxLengthEditCommand = new EditMaxLengthCommand();
        $labelEditCommand = new EditLabelsCommand();

        $this->supports($textAttribute, $maxLengthEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $maxLengthEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_max_length_property_of_a_text_attribute(TextAttribute $textAttribute)
    {
        $editMaxLength = new EditMaxLengthCommand();
        $editMaxLength->maxLength = 200;
        $textAttribute->setMaxLength(AttributeMaxLength::fromInteger(200));
        $this->__invoke($textAttribute, $editMaxLength);
    }

    function it_edits_updates_the_max_length_property_to_set_it_infinite(TextAttribute $textAttribute)
    {
        $editMaxLength = new EditMaxLengthCommand();
        $editMaxLength->maxLength = null;
        $textAttribute->setMaxLength(AttributeMaxLength::infinite());
        $this->__invoke($textAttribute, $editMaxLength);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $rightAttribute, ImageAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand();
        $rightCommand = new EditMaxLengthCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
