<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ScalarComparatorSpec extends ObjectBehavior
{
    function let(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute,
        PropertyAccessor $accessor
    ) {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith($accessor);
    }

    function it_supports_comparison_of_null_value($value)
    {
        $value->getData()->willReturn(null);
        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_supports_comparison_of_scalar_value($value)
    {
        $value->getData()->willReturn('foo');
        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_does_not_support_comparison_of_non_scalar_value($value, $object)
    {
        $value->getData()->willReturn($object);
        $this->supportsComparison($value)->shouldBe(false);
    }

    function it_detects_changes_on_scalar_value(
        $accessor,
        $value
    ) {
        $submittedData = [
            'varchar' => 'foo',
        ];
        $accessor->getValue($value, 'varchar')->willReturn('bar');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'varchar' => 'foo',
        ]);
    }

    function it_detects_no_changes_on_scalar_value(
        $accessor,
        $value
    ) {
        $submittedData = [
            'varchar' => 'foo',
        ];
        $accessor->getValue($value, 'varchar')->willReturn('foo');

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_change_when_there_nothing_else_than_the_id_in_the_changes(
        $value
    ) {
        $submittedData = [];

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
