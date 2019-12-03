<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\RegularExpressionUpdater;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class RegularExpressionUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RegularExpressionUpdater::class);
    }

    function it_only_supports_edit_regular_expressions_command_for_text_attributes(
        TextAttribute $textAttribute,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand('name', []);
        $editRegularExpression = new EditRegularExpressionCommand('name', null);

        $this->supports($textAttribute, $editRegularExpression)->shouldReturn(true);
        $this->supports($mediaFileAttribute, $editRegularExpression)->shouldReturn(false);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_regular_expression_of_a_text_attribute(TextAttribute $textAttribute)
    {
        $editRequired = new EditRegularExpressionCommand('name', '/\w+/');
        $textAttribute->setRegularExpression(AttributeRegularExpression::fromString('/\w+/'))->shouldBeCalled();
        $this->__invoke($textAttribute, $editRequired)->shouldReturn($textAttribute);
    }

    function it_sets_the_regular_expression_to_none(TextAttribute $textAttribute)
    {
        $editRequired = new EditRegularExpressionCommand('name', null);
        $textAttribute->setRegularExpression(AttributeRegularExpression::createEmpty())->shouldBeCalled();
        $this->__invoke($textAttribute, $editRequired)->shouldReturn($textAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
