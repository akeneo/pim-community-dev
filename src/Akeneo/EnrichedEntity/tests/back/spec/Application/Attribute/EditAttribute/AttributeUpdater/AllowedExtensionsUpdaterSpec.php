<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater\AllowedExtensionsUpdater;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAllowedExtensionsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class AllowedExtensionsUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AllowedExtensionsUpdater::class);
    }

    function it_only_supports_edit_allowed_extensions_command_of_image_attributes(
        TextAttribute $textAttribute,
        ImageAttribute $imageAttribute
    ) {
        $allowedExtensionsEditCommand = new EditAllowedExtensionsCommand();
        $labelEditCommand = new EditLabelsCommand();

        $this->supports($imageAttribute, $allowedExtensionsEditCommand)->shouldReturn(true);
        $this->supports($imageAttribute, $labelEditCommand)->shouldReturn(false);
        $this->supports($textAttribute, $allowedExtensionsEditCommand)->shouldReturn(false);
    }

    function it_edits_the_allowed_extensions_property_of_an_image_attribute(ImageAttribute $imageAttribute)
    {
        $editAllowedExtensions = new EditAllowedExtensionsCommand();
        $editAllowedExtensions->allowedExtensions = ['png'];
        $imageAttribute->setAllowedExtensions(AttributeAllowedExtensions::fromList(['png']))->shouldBeCalled();
        $this->__invoke($imageAttribute, $editAllowedExtensions)->shouldReturn($imageAttribute);
    }

    function it_throws_if_it_cannot_update_the_attribute(ImageAttribute $rightAttribute, TextAttribute $wrongAttribute)
    {
        $wrongCommand = new EditLabelsCommand();
        $rightCommand = new EditAllowedExtensionsCommand();
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$rightAttribute, $wrongCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $rightCommand]);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$wrongAttribute, $wrongCommand]);
    }
}
