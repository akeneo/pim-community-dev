<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

class ArrayGuesserSpec extends ObjectBehavior
{
    function let(
        PropertyTransformerInterface $transformer,
        ColumnInfoInterface $columnInfo,
        ClassMetadataInfo $metadata
    ) {
        $this->beConstructedWith($transformer, 'array');
        $columnInfo->getPropertyPath()->willReturn('property_path');
        $metadata->hasField('property_path')->willReturn(true);
        $metadata->getTypeOfField('property_path')->willReturn('array');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\Guesser\ArrayGuesser');
    }

    function it_should_skip_columns_without_suffixes(
        ColumnInfoInterface $columnInfo,
        ClassMetadataInfo $metadata
    ) {
        $columnInfo->getSuffixes()->willReturn(array());
        $this->getTransformerInfo($columnInfo, $metadata)->shouldReturn(null);
    }

    function it_should_return_a_transformer_for_column_with_suffixes(
        PropertyTransformerInterface $transformer,
        ColumnInfoInterface $columnInfo,
        ClassMetadataInfo $metadata
    ) {
        $columnInfo->getSuffixes()->willReturn(array('suffix'));
        $this->getTransformerInfo($columnInfo, $metadata)->shouldReturn(array($transformer, array()));
    }
}
