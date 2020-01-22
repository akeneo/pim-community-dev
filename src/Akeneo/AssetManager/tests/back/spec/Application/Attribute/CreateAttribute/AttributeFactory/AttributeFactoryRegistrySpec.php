<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryInterface;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistry;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use PhpSpec\ObjectBehavior;

class AttributeFactoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeFactoryRegistry::class);
    }

    public function it_registers_attribute_factories_and_returns_the_factory_corresponding_to_the_command(
        AttributeFactoryInterface $factory
    ) {
        $command = new CreateTextAttributeCommand(
            'designer',
            'color',
            [],
            false,
            false,
            false,
            false,
            null,
            false,
            false,
            null,
            null
        );
        $factory->supports($command)->willReturn(true);
        $this->register($factory);
        $this->getFactory($command)->shouldReturn($factory);
    }

    public function it_throws_if_the_corresponding_factory_is_not_found()
    {
        $this->shouldThrow(new \RuntimeException(
            'There was no attribute factory found for command "Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand"')
        )->during('getFactory', [
            new CreateMediaFileAttributeCommand(
                'designer',
                'color',
                [],
                false,
                false,
                false,
                false,
                null,
                [],
                MediaType::IMAGE
            )
        ]);
    }
}

