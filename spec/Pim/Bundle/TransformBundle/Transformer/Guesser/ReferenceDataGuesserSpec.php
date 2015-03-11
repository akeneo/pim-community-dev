<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;
use Prophecy\Argument;

class ReferenceDataGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\Guesser\ReferenceDataGuesser');
    }

    function let(PropertyTransformerInterface $transformer, ManagerRegistry $doctrine)
    {
        $this->beConstructedWith($transformer, $doctrine, 'ProductValueClass');
    }

    function it_gets_null_for_non_reference_data_column_info(ColumnInfoInterface $columnInfo, ClassMetadata $metadata)
    {
        $metadata->getName()->willReturn('NotSupportedClass');
        $this->getTransformerInfo($columnInfo, $metadata)->shouldReturn(null);

        $metadata->getName()->willReturn('ProductValueClass');
        $columnInfo->getPropertyPath()->wilLReturn('not-supported-backend-type');

        $this->getTransformerInfo($columnInfo, $metadata)->shouldReturn(null);
    }

    function it_gets_reference_data_transformer_info(
        ColumnInfoInterface $columnInfo,
        ClassMetadata $metadata,
        AttributeInterface $attribute
    ) {
        $metadata->getName()->willReturn('ProductValueClass');
        $columnInfo->getPropertyPath()->wilLReturn('reference_data_option');
        $columnInfo->getAttribute()->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('color');

        $columnInfo->setPropertyPath('color')->shouldBeCalled();

        $this->getTransformerInfo($columnInfo, $metadata);
    }
}
