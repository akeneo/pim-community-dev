<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Pim\Bundle\CatalogBundle\Model;

class ScalarComparatorSpec extends ObjectBehavior
{
    function let(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute,
        PropertyAccessor $accessor
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getId()->willReturn(713705);
        $value->getScope()->willReturn('ecommerce');
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getId()->willReturn(1337);

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
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
            ],
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
