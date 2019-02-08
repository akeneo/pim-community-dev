<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\IsTextareaUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextareaCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class IsTextareaUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsTextareaUpdater::class);
    }

    function it_only_supports_edit_is_textarea_flag_for_text_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand('name', []);
        $isRequiredEditCommand = new EditIsTextareaCommand('name', true);

        $this->supports($textAttribute, $isRequiredEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $isRequiredEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_is_textarea_flag_of_a_text_attribute(TextAttribute $textAttribute)
    {
        $editRequired = new EditIsTextareaCommand('name', false);
        $textAttribute->setIsTextarea(AttributeIsTextarea::fromBoolean(false))->shouldBeCalled();
        $this->__invoke($textAttribute, $editRequired)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
