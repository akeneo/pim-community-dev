<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\Property;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ArrayTransformerSpec extends ObjectBehavior
{
    function let(PropertyAccessorInterface $propertyAccessor)
    {
        $this->beConstructedWith($propertyAccessor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\Property\ArrayTransformer');
    }

    function it_calls_the_property_accessor($propertyAccessor, \stdClass $object, ColumnInfoInterface $columnInfo)
    {
        $propertyAccessor->setValue($object, 'property_path[suffix]', 'value')->shouldBeCalled();
        $columnInfo->getPropertyPath()->willReturn('property_path');
        $columnInfo->getSuffixes()->willReturn(array('suffix'));
        $this->setValue($object, $columnInfo, 'value');
    }
}
