<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class ScalarComparatorSpec extends ObjectBehavior
{
    public function let(PropertyAccessor $accessor)
    {
        $this->beConstructedWith($accessor);
    }

    function it_supports_comparison_of_null_value(AbstractProductValue $value)
    {
        $value->getData()->willReturn(null);
        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_supports_comparison_of_scalar_value(AbstractProductValue $value)
    {
        $value->getData()->willReturn('foo');
        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_does_not_support_comparison_of_non_scalar_value(AbstractProductValue $value, $object)
    {
        $value->getData()->willReturn($object);
        $this->supportsComparison($value)->shouldBe(false);
    }

    function it_detects_changes_on_scalar_value(
        $accessor,
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => 1,
            'varchar' => 'foo',
        ];
        $accessor->getValue($value, 'varchar')->willReturn('bar');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => 1,
            'varchar' => 'foo'
        ]);
    }

    function it_detects_no_changes_on_scalar_value(
        $accessor,
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => 1,
            'varchar' => 'foo',
        ];
        $accessor->getValue($value, 'varchar')->willReturn('foo');

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_change_when_there_nothing_else_than_the_id_in_the_changes(
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => 1,
        ];

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
