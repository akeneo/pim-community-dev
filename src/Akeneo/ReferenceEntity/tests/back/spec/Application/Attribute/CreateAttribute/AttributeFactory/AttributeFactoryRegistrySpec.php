<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistry;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
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
        $this->shouldThrow(
            new \RuntimeException(
                'There was no attribute factory found for command "Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand"'
            )
        )->during(
            'getFactory',
            [
                new CreateImageAttributeCommand(
                    'designer',
                    'color',
                    [],
                    false,
                    false,
                    false,
                    null,
                    []
                ),
            ]
        )
        ;
    }
}

