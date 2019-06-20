<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use PhpSpec\ObjectBehavior;

class AttributeHydratorRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeHydratorRegistry::class);
    }

    function it_registers_multiple_hydrators_and_returns_them(
        AttributeHydratorInterface $attributeHydrator1,
        AttributeHydratorInterface $attributeHydrator2
    ) {
        $attributeHydrator1->supports(['hydrate_1'])->willReturn(true);
        $attributeHydrator1->supports(['hydrate_2'])->willReturn(false);
        $attributeHydrator2->supports(['hydrate_2'])->willReturn(true);
        $this->register($attributeHydrator1);
        $this->register($attributeHydrator2);
        $this->getHydrator(['hydrate_1'])->shouldReturn($attributeHydrator1);
        $this->getHydrator(['hydrate_2'])->shouldReturn($attributeHydrator2);
    }

    function it_throws_if_it_does_not_find_a_hydrator()
    {
        $this->shouldThrow(\RuntimeException::class)->during('getHydrator', [['unknown_hydrator']]);
    }
}
