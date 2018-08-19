<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryRegistry;
use PhpSpec\ObjectBehavior;

class EditAttributeCommandFactoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeCommandFactoryRegistry::class);
    }

    public function it_registers_edit_attribute_command_factories_and_returns_the_corresponding_factory(
        EditAttributeCommandFactoryInterface $factory
    ) {
        $normalizedCommand = ['required' => 'true'];
        $factory->supports($normalizedCommand)->willReturn(true);
        $this->register($factory);
        $this->getFactory($normalizedCommand)->shouldReturn($factory);
    }

    public function it_throws_if_the_corresponding_factory_is_not_found()
    {
        $this->shouldThrow(\RuntimeException::class)->during('getFactory', [['type' => 'unsupported_type']]);
    }
}
