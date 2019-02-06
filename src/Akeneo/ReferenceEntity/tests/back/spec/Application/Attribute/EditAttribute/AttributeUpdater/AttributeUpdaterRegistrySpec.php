<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterRegistry;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\MaxLengthUpdater;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class AttributeUpdaterRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeUpdaterRegistry::class);
    }

    function it_register_an_attribute_updater_and_returns_it_if_it_supports(TextAttribute $name)
    {
        $updater = new MaxLengthUpdater();
        $this->register($updater);
        $this->getUpdater($name, new EditMaxLengthCommand('name', 125))->shouldReturn($updater);
    }

    function it_throws_if_it_does_not_finds_an_updater_that_supports(TextAttribute $name)
    {
        $this->shouldThrow(\RuntimeException::class)->during('getUpdater', [$name, new EditMaxLengthCommand('name', 125)]);
    }
}
