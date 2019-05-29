<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\MinMaxValueUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMinMaxValueCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class MinMaxValueUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MinMaxValueUpdater::class);
        $this->shouldBeAnInstanceOf(AttributeUpdaterInterface::class);
    }

    function it_only_supports_edit_min_value_command_for_number_attributes(
        NumberAttribute $numberAttribute,
        TextAttribute $textAtttribute
    ) {
        $editMinCommand = new EditMinMaxValueCommand('number', '10', null);
        $notEditMinCommand = new EditLabelsCommand('name', []);

        $this->supports($numberAttribute, $editMinCommand)->shouldReturn(true);
        $this->supports($numberAttribute, $notEditMinCommand)->shouldReturn(false);
        $this->supports($textAtttribute, $editMinCommand)->shouldReturn(false);
    }

    function it_edits_the_min_max_value_of_an_attribute(NumberAttribute $numberAttribute)
    {
        $editMinMax = new EditMinMaxValueCommand('min', '10', '12');

        $this->__invoke($numberAttribute, $editMinMax)->shouldReturn($numberAttribute);

        $numberAttribute->setLimit(AttributeLimit::fromString('10'),AttributeLimit::fromString('12'))
            ->shouldBeCalled();
    }

    function it_unsets_the_min_max_max_value_of_an_attribute(NumberAttribute $numberAttribute)
    {
        $editMin = new EditMinMaxValueCommand('min', null, null);

        $this->__invoke($numberAttribute, $editMin)->shouldReturn($numberAttribute);

        $numberAttribute->setLimit(AttributeLimit::limitless(),AttributeLimit::limitless())->shouldBeCalled();
    }

    function it_throws_if_the_command_is_not_supported(NumberAttribute $numberAttribute)
    {
        $unsupportedCommand = new EditLabelsCommand( 'min', []);

        $this->shouldThrow(\RuntimeException::class)
            ->during('__invoke', [$numberAttribute, $unsupportedCommand]);
    }

    function it_throws_if_the_attribute_is_not_supported(TextAttribute $unsupportedAttribute)
    {
        $supportedCommand = new EditMinMaxValueCommand('min', null, null);

        $this->shouldThrow(\RuntimeException::class)
            ->during('__invoke', [$unsupportedAttribute, $supportedCommand]);
    }
}
