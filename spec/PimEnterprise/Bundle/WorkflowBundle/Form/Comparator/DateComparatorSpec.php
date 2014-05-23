<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class DateComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_comparison_of_date_attribute(
        AbstractProductValue $value,
        AbstractAttribute $attribute
    )
    {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_date');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_get_changes_when_updating_the_date(
        AbstractProductValue $value,
        \DateTime $date
    ) {
        $submittedData = [
            'id' => 123,
            'date' => '1987-05-14'
        ];
        $value->getDate()->willReturn($date);
        $date->format('Y-m-d')->willReturn('2014-05-18');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'date' => '1987-05-14'
        ]);
    }

    function it_does_not_detect_changes_when_submitted_date_is_the_same(
        AbstractProductValue $value,
        \DateTime $date
    ) {
        $submittedData = [
            'date' => '1987-05-14',
        ];
        $value->getDate()->willReturn($date);
        $date->format('Y-m-d')->willReturn('1987-05-14');

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_date_change_when_the_current_date_is_empty_and_the_new_date_is_not(
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => 123,
            'date' => '1987-05-14'
        ];
        $value->getDate()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'date' => '1987-05-14'
        ]);
    }

    function it_does_not_detect_changes_when_the_current_date_is_empty_and_the_new_one_also(
        AbstractProductValue $value
    ) {
        $submittedData = [
            'id' => 123,
            'date' => '',
        ];
        $value->getDate()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
