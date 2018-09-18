<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\LabelsUpdater;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class LabelsUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(LabelsUpdater::class);
    }

    function it_only_supports_edit_labels_command_for_all_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand();
        $isRequiredEditCommand = new EditIsRequiredCommand();

        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $labelEditCommand)->shouldReturn(true);
        $this->supports($textAttribute, $isRequiredEditCommand)->shouldReturn(false);
    }

    function it_edits_the_labels_of_an_attribute(TextAttribute $textAttribute)
    {
        $labelEditCommand = new EditLabelsCommand();
        $labelEditCommand->labels = ['fr_FR' => 'Traduction francaise'];
        $textAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Traduction francaise']))->shouldBeCalled();
        $this->__invoke($textAttribute, $labelEditCommand)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditIsRequiredCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
