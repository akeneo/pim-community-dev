<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;

class ChainedComparatorSpec extends ObjectBehavior
{
    public function let(
        ComparatorInterface $comparator1,
        ComparatorInterface $comparator2
    ) {
        $this->addComparator($comparator1, 0);
        $this->addComparator($comparator2, 100);
    }
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_is_a_root_comparator(AbstractProductValue $value)
    {
        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_has_comparators($comparator1, $comparator2)
    {
        $this->getComparators()->shouldReturn([$comparator2, $comparator1]);
    }

    function it_delegates_comparison_resolution_to_embedded_comparators(
        AbstractProductValue $value,
        $comparator1,
        $comparator2
    ) {
        $comparator2->supportsComparison($value)->willReturn(false);
        $comparator1->supportsComparison($value)->willReturn(true);
        $comparator1->getChanges($value, ['foo' => 'bar'])->willReturn(['bar']);

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
