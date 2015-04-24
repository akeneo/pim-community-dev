<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

class ReferenceDataGuesserSpec extends ObjectBehavior
{
    function let(PropertyTransformerInterface $transformer)
    {
        $this->beConstructedWith($transformer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\Guesser\ReferenceDataGuesser');
    }

    function it_returns_transformer_if_property_is_reference_data(
        ColumnInfoInterface $columnInfo,
        ClassMetadataInfo $metadata,
        PropertyTransformerInterface $transformer
    ) {
        $columnInfo->getPropertyPath()->willReturn('referenceDataName');
        $this->getTransformerInfo($columnInfo, $metadata)->shouldReturn([$transformer, []]);
    }

    function it_returns_null_if_property_is_not_reference_data(
        ColumnInfoInterface $columnInfo,
        ClassMetadataInfo $metadata,
        PropertyTransformerInterface $transformer
    ) {
        $columnInfo->getPropertyPath()->willReturn('other');
        $this->getTransformerInfo($columnInfo, $metadata)->shouldReturn(null);
    }
}
