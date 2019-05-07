<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\MaxValueUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxValueCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class MaxValueUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MaxValueUpdater::class);
        $this->shouldBeAnInstanceOf(AttributeUpdaterInterface::class);
    }

    function it_only_supports_edit_max_value_command_for_number_attributes(
        NumberAttribute $numberAttribute,
        TextAttribute $unsupportedAttribute
    ) {
        $editMaxCommand = new EditMaxValueCommand('number', '10');
        $notEditMaxCommand = new EditLabelsCommand('name', []);

        $this->supports($numberAttribute, $editMaxCommand)->shouldReturn(true);
        $this->supports($numberAttribute, $notEditMaxCommand)->shouldReturn(false);
        $this->supports($unsupportedAttribute, $editMaxCommand)->shouldReturn(false);
    }

    function it_edits_the_max_value_of_an_attribute(NumberAttribute $numberAttribute)
    {
        $editMax = new EditMaxValueCommand('max', '10');

        $this->__invoke($numberAttribute, $editMax)->shouldReturn($numberAttribute);

        $numberAttribute->setMaxValue(AttributeLimit::fromString('10'))->shouldBeCalled();
    }

    function it_unsets_the_max_value_of_an_attribute(NumberAttribute $numberAttribute)
    {
        $editMax = new EditMaxValueCommand('max', null);

        $this->__invoke($numberAttribute, $editMax)->shouldReturn($numberAttribute);

        $numberAttribute->setMaxValue(AttributeLimit::limitLess())->shouldBeCalled();
    }

    function it_throws_if_the_command_is_not_supported(NumberAttribute $numberAttribute)
    {
        $unsupportedCommand = new EditLabelsCommand( 'max', []);

        $this->shouldThrow(\RuntimeException::class)
            ->during('__invoke', [$numberAttribute, $unsupportedCommand]);
    }

    function it_throws_if_the_attribute_is_not_supported(TextAttribute $unsupportedAttribute)
    {
        $supportedCommand = new EditMaxValueCommand('max', null);

        $this->shouldThrow(\RuntimeException::class)
            ->during('__invoke', [$unsupportedAttribute, $supportedCommand]);
    }
}
