<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;

class ChainedComparatorSpec extends ObjectBehavior
{
    function let(ComparatorInterface $comparator1, ComparatorInterface $comparator2)
    {
        $this->addComparator($comparator1, 0);
        $this->addComparator($comparator2, 100);
    }

    function it_has_comparators($comparator1, $comparator2)
    {
        $this->getComparators()->shouldReturn([$comparator2, $comparator1]);
    }

    function it_delegates_comparison_resolution_to_embedded_comparators($comparator1, $comparator2)
    {
        $attribute = 'pim_catalog_text';
        $comparator1->supportsComparison($attribute)->willReturn(true);
        $comparator1->getChanges(['foo' => 'bar'], ['foo' => 'bar'])->willReturn(['bar']);

        $this->compare($attribute, ['foo' => 'bar'], ['foo' => 'bar'])->shouldReturn(['bar']);
    }

    function it_throws_exception_when_no_eligible_comparator_is_available()
    {
        $exception = new \LogicException(
            'Cannot compare value of attribute type "pim_catalog_fancy". ' .
            'Please check that a comparator exists for such attribute type.'
        );
        $this->shouldThrow($exception)->duringCompare('pim_catalog_fancy', [], []);
    }
}
