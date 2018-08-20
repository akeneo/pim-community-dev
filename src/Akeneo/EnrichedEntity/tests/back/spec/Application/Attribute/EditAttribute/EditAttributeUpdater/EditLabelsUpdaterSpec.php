<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeUpdater\EditLabelsUpdater;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditLabelsUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditLabelsUpdater::class);
    }

    function it_only_supports_edit_labels_command_for_all_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand();
        $requiredEditCommand = new EditRequiredCommand();

        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $labelEditCommand)->shouldReturn(true);
        $this->supports($textAttribute, $requiredEditCommand)->shouldReturn(false);
    }

    function it_edits_the_labels_of_an_attribute(TextAttribute $textAttribute) {
        $labelEditCommand = new EditLabelsCommand();
        $labelEditCommand->labels = ['fr_FR' => 'Traduction francaise'];
        $textAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Traduction francaise']))->shouldBeCalled();
        $this->__invoke($textAttribute, $labelEditCommand);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute) {
        $wrongCommand = new EditRequiredCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
