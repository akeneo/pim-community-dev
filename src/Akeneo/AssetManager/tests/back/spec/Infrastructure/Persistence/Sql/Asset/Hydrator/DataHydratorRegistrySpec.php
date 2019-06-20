<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\DataHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\DataHydratorRegistry;
use PhpSpec\ObjectBehavior;

class DataHydratorRegistrySpec extends ObjectBehavior
{
    function it_it_initializable()
    {
        $this->shouldHaveType(DataHydratorRegistry::class);
    }

    function it_registers_a_data_hydrator_and_returns_it(
        AbstractAttribute $attributeA,
        AbstractAttribute $attributeB,
        DataHydratorInterface $dataHydratorA,
        DataHydratorInterface $dataHydratorB
    )
    {
        $dataHydratorA->supports($attributeA)->willReturn(true);
        $dataHydratorA->supports($attributeB)->willReturn(false);

        $dataHydratorB->supports($attributeA)->willReturn(false);
        $dataHydratorB->supports($attributeB)->willReturn(true);

        $this->register($dataHydratorA);
        $this->register($dataHydratorB);

        $this->getHydrator($attributeA)->shouldReturn($dataHydratorA);
        $this->getHydrator($attributeB)->shouldReturn($dataHydratorB);
    }

    function it_throws_an_exception_if_no_hydrator_is_found(AbstractAttribute $attribute)
    {
        $attribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('name'));

        $this->shouldThrow(\RuntimeException::class)->during('getHydrator', [$attribute]);
    }
}
