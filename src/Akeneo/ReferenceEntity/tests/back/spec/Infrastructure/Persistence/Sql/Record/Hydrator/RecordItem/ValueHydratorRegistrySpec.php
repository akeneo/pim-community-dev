<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ValueHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ValueHydratorRegistry;
use PhpSpec\ObjectBehavior;

class ValueHydratorRegistrySpec extends ObjectBehavior
{
    function it_it_initializable()
    {
        $this->shouldHaveType(ValueHydratorRegistry::class);
    }

    function it_registers_a_value_hydrator_hydrates_with_its(
        AbstractAttribute $attributeA,
        AbstractAttribute $attributeB,
        ValueHydratorInterface $valueHydratorA,
        ValueHydratorInterface $valueHydratorB
    ) {
        $valueHydratorA->supports($attributeA)->willReturn(true);
        $valueHydratorA->supports($attributeB)->willReturn(false);

        $valueHydratorB->supports($attributeA)->willReturn(false);
        $valueHydratorB->supports($attributeB)->willReturn(true);

        $this->register($valueHydratorA);
        $this->register($valueHydratorB);

        $valueA = [
            'attribute' => 'attribute-a-abcdef123456789',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'data' => 'The data',
        ];

        $valueB = [
            'attribute' => 'attribute-b-abcdef123456789',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'data' => 'The data',
        ];

        $valueHydratorA->hydrate($valueA, $attributeA, [])->willReturn($valueA + ['context' => 'Nice']);
        $valueHydratorB->hydrate($valueB, $attributeB, [])->willReturn($valueB + ['other' => 'Random']);

        $this->hydrate($valueA, $attributeA, [])->shouldReturn($valueA + ['context' => 'Nice']);
        $this->hydrate($valueB, $attributeB, [])->shouldReturn($valueB + ['other' => 'Random']);
    }

    function it_returns_the_original_value_if_no_hydrator_supports_it(
        AbstractAttribute $attributeA,
        AbstractAttribute $attributeB
    ) {
        $value = [
            'attribute' => 'description-abcdef123456789',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'data' => 'This is a description',
        ];

        $this->hydrate($value, $attributeA)->shouldReturn($value);
        $this->hydrate($value, $attributeB)->shouldReturn($value);
    }
}
