<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\RegularExpressionUpdater;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class RegularExpressionUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RegularExpressionUpdater::class);
    }

    function it_only_supports_edit_regular_expressions_command_for_text_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand();
        $editRegularExpression = new EditRegularExpressionCommand();

        $this->supports($textAttribute, $editRegularExpression)->shouldReturn(true);
        $this->supports($imageAttribute, $editRegularExpression)->shouldReturn(false);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_regular_expression_of_a_text_attribute(TextAttribute $textAttribute)
    {
        $editRequired = new EditRegularExpressionCommand();
        $editRequired->regularExpression = '/\w+/';
        $textAttribute->setRegularExpression(AttributeRegularExpression::fromString('/\w+/'))->shouldBeCalled();
        $this->__invoke($textAttribute, $editRequired)->shouldReturn($textAttribute);
    }

    function it_sets_the_regular_expression_to_none(TextAttribute $textAttribute)
    {
        $editRequired = new EditRegularExpressionCommand();
        $editRequired->regularExpression = null;
        $textAttribute->setRegularExpression(AttributeRegularExpression::none())->shouldBeCalled();
        $this->__invoke($textAttribute, $editRequired)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditLabelsCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
