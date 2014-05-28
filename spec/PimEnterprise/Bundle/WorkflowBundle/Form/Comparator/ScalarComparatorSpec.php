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

    function it_always_supports_comparison(AbstractProductValue $value)
    {
        $this->supportsComparison($value)->shouldBe(true);
    }
}
