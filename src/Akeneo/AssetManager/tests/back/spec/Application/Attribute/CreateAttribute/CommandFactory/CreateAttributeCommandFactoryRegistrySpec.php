<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryInterface;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistry;
use PhpSpec\ObjectBehavior;

class CreateAttributeCommandFactoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAttributeCommandFactoryRegistry::class);
    }

    public function it_registers_create_attribute_command_factories_and_returns_the_corresponding_factory(
        CreateAttributeCommandFactoryInterface $factory
    ) {
        $normalizedCommand = ['type' => 'text'];
        $factory->supports($normalizedCommand)->willReturn(true);
        $this->register($factory);
        $this->getFactory($normalizedCommand)->shouldReturn($factory);
    }

    public function it_throws_if_the_corresponding_factory_is_not_found()
    {
        $this->shouldThrow(\RuntimeException::class)->during('getFactory', [['type' => 'unsupported_type']]);
    }
}
