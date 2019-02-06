<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\IsRequiredUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class IsRequiredUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsRequiredUpdater::class);
    }

    function it_only_supports_edit_required_command_for_all_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand(
            'name',
            []
        );
        $isRequiredEditCommand = new EditIsRequiredCommand(
            'name',
            false
        );

        $this->supports($textAttribute, $isRequiredEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $isRequiredEditCommand)->shouldReturn(true);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_required_property_of_an_attribute(TextAttribute $textAttribute)
    {
        $editRequired = new EditIsRequiredCommand(
            'name',
            false
        );
        $textAttribute->setIsRequired(AttributeIsRequired::fromBoolean(false))->shouldBeCalled();
        $this->__invoke($textAttribute, $editRequired)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditLabelsCommand(
            'name',
            []
        );
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]
        );
    }
}
