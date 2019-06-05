<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\DecimalsAllowedUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditDecimalsAllowedCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class DecimalsAllowedUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DecimalsAllowedUpdater::class);
    }

    function it_only_supports_edit_decimal_command_for_all_attributes(
        TextAttribute $textAttribute,
        NumberAttribute $numberAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand(
            'name',
            []
        );
        $decimalsAllowedCommand = new EditDecimalsAllowedCommand(
            'age',
            false
        );

        $this->supports($numberAttribute, $decimalsAllowedCommand)->shouldReturn(true);
        $this->supports($textAttribute, $decimalsAllowedCommand)->shouldReturn(false);
        $this->supports($numberAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_decimal_property_of_an_attribute(NumberAttribute $numberAttribute)
    {
        $editRequired = new EditDecimalsAllowedCommand(
            'age',
            false
        );
        $numberAttribute->setDecimalsAllowed(AttributeDecimalsAllowed::fromBoolean(false))->shouldBeCalled();
        $this->__invoke($numberAttribute, $editRequired)->shouldReturn($numberAttribute);
    }
}
