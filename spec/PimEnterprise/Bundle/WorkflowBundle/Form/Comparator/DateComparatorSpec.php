<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;

class DateComparatorSpec extends ObjectBehavior
{
    function let(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getId()->willReturn(713705);
        $value->getScope()->willReturn('ecommerce');
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getId()->willReturn(1337);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_comparison_of_date_attribute(
        $value,
        $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_date');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_get_changes_when_updating_the_date(
        $value,
        \DateTime $date
    ) {
        $submittedData = [
            'date' => '1987-05-14'
        ];
        $value->getDate()->willReturn($date);
        $date->format('Y-m-d')->willReturn('2014-05-18');

        $this->getChanges($value, $submittedData)->shouldReturn([
            'date' => '1987-05-14',
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
            ],
        ]);
    }

    function it_does_not_detect_changes_when_submitted_date_is_the_same(
        $value,
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
        $value
    ) {
        $submittedData = [
            'date' => '1987-05-14'
        ];
        $value->getDate()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'date' => '1987-05-14',
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
            ],
        ]);
    }

    function it_does_not_detect_changes_when_the_current_date_is_empty_and_the_new_one_also(
        $value
    ) {
        $submittedData = [
            'date' => '',
        ];
        $value->getDate()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_does_not_detect_changes_when_the_new_date_is_not_defined(
        $value
    ) {
        $submittedData = [];
        $value->getDate()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
