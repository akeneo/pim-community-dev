<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\IsDecimalUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsDecimalCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsDecimal;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class IsDecimalUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsDecimalUpdater::class);
    }

    function it_only_supports_edit_decimal_command_for_all_attributes(
        TextAttribute $textAttribute,
        NumberAttribute $numberAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand(
            'name',
            []
        );
        $isDecimalEditCommand = new EditIsDecimalCommand(
            'age',
            false
        );

        $this->supports($numberAttribute, $isDecimalEditCommand)->shouldReturn(true);
        $this->supports($textAttribute, $isDecimalEditCommand)->shouldReturn(false);
        $this->supports($numberAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_decimal_property_of_an_attribute(NumberAttribute $numberAttribute)
    {
        $editRequired = new EditIsDecimalCommand(
            'age',
            false
        );
        $numberAttribute->setIsDecimal(AttributeIsDecimal::fromBoolean(false))->shouldBeCalled();
        $this->__invoke($numberAttribute, $editRequired)->shouldReturn($numberAttribute);
    }
}
