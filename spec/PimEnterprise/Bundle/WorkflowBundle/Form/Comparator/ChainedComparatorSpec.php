<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;

class ChainedComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_is_a_root_comparator(AbstractProductValue $value)
    {
        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_delegates_comparison_resolution_to_embedded_comparators(
        AbstractProductValue $value,
        ComparatorInterface $comparator1,
        ComparatorInterface $comparator2
    ) {
        $comparator1->supportsComparison($value)->willReturn(false);
        $comparator2->supportsComparison($value)->willReturn(true);
        $comparator2->getChanges($value, ['foo' => 'bar'])->willReturn(['bar']);

        $this->addComparator($comparator1);
        $this->addComparator($comparator2);
        $this->getChanges($value, ['foo' => 'bar'])->shouldReturn(['bar']);
    }

    function it_throws_exception_when_no_eligible_comparator_is_available(
        AbstractProductValue $value,
        AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_fancy');

        $exception = new \LogicException(
            'Cannot compare value of attribute type "pim_catalog_fancy". Please check that a comparator exists for such attribute type.'
        );
        $this->shouldThrow($exception)->duringGetChanges($value, ['foo' => 'bar']);
    }
}
