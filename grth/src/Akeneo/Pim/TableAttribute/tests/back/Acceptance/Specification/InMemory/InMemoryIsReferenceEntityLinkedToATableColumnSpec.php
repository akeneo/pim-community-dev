<?php

namespace Specification\Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsReferenceEntityLinkedToATableColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory\InMemoryIsReferenceEntityLinkedToATableColumn;
use PhpSpec\ObjectBehavior;

class InMemoryIsReferenceEntityLinkedToATableColumnSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryIsReferenceEntityLinkedToATableColumn::class);
        $this->shouldImplement(IsReferenceEntityLinkedToATableColumn::class);
    }

    public function it_returns_true_if_reference_entity_is_linked_to_a_table_attribute(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findBy(['attributeType' => AttributeTypes::TABLE])->shouldBeCalled()->willReturn(
            [$attribute]
        );
        $attribute->getRawTableConfiguration()->shouldBeCalled()->willReturn([
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'record', 'reference_entity_identifier' => 'brands'],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'number'],
        ]);
        $this->forIdentifier('brands')->shouldBe(true);
    }

    public function it_returns_true_if_reference_entity_is_linked_to_a_table_attribute_case_insensitive(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findBy(['attributeType' => AttributeTypes::TABLE])->shouldBeCalled()->willReturn(
            [$attribute]
        );
        $attribute->getRawTableConfiguration()->shouldBeCalled()->willReturn([
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'record', 'reference_entity_identifier' => 'BRAnds'],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'number'],
        ]);
        $this->forIdentifier('brands')->shouldBe(true);
    }

    public function it_returns_false_if_reference_entity_is_not_linked_to_a_table_attribute(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
        AttributeInterface $otherAttribute
    ) {
        $attributeRepository->findBy(['attributeType' => AttributeTypes::TABLE])->shouldBeCalled()->willReturn(
            [$attribute, $otherAttribute]
        );
        $attribute->getRawTableConfiguration()->shouldBeCalled()->willReturn([
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'select', 'options' => []],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'number'],
        ]);
        $otherAttribute->getRawTableConfiguration()->shouldBeCalled()->willReturn([
            ['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'select', 'reference_entity_identifier' => 'designer'],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'number'],
        ]);

        $this->forIdentifier('brands')->shouldBe(false);
    }
}
