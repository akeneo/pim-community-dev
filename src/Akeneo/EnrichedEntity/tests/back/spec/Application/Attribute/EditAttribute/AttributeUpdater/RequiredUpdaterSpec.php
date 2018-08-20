<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\RequiredUpdater;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class RequiredUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RequiredUpdater::class);
    }

    function it_only_supports_edit_required_command_for_all_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand();
        $requiredEditCommand = new EditRequiredCommand();

        $this->supports($textAttribute, $requiredEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $requiredEditCommand)->shouldReturn(true);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_required_property_of_an_attribute(TextAttribute $textAttribute)
    {
        $editRequired = new EditRequiredCommand();
        $editRequired->required = false;
        $textAttribute->setIsRequired(AttributeRequired::fromBoolean(false))->willReturn($textAttribute);
        $this->__invoke($textAttribute, $editRequired);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditLabelsCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
