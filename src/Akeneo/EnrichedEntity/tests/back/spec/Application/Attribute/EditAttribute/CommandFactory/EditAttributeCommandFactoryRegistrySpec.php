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

    public function it_registers_edit_attribute_command_factories_and_returns_the_corresponding_factories(
        EditAttributeCommandFactoryInterface $supportedFactory1,
        EditAttributeCommandFactoryInterface $supportedFactory2,
        EditAttributeCommandFactoryInterface $notSupportedFactory
    ) {
        $normalizedCommand = ['required' => 'true', 'labels' => ['fr_FR' => 'A label'], 'max_length' => 155];
        $supportedFactory1->supports($normalizedCommand)->willReturn(true);
        $supportedFactory2->supports($normalizedCommand)->willReturn(true);
        $notSupportedFactory->supports($normalizedCommand)->willReturn(false);

        $this->register($supportedFactory1);
        $this->register($supportedFactory2);
        $this->register($notSupportedFactory);

        $this->getFactories($normalizedCommand)->shouldReturn([$supportedFactory1, $supportedFactory2]);
    }

    public function it_throws_if_the_corresponding_factory_is_not_found()
    {
        $this->shouldThrow(\RuntimeException::class)->during('getFactories', [['type' => 'unsupported_type']]);
    }
}
