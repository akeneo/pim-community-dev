<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Comparator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ReferenceDataComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_a_simple_reference_data(ProductValueInterface $productValue)
    {
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_simpleselect');

        $productValue->getAttribute()->willReturn($attribute);
        $this->supportsComparison($productValue)->shouldBe(true);
    }

    function it_does_not_support_a_non_simple_reference_data(ProductValueInterface $productValue)
    {
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_multiselect');

        $productValue->getAttribute()->willReturn($attribute);
        $this->supportsComparison($productValue)->shouldBe(false);

        $attribute->setAttributeType('other');

        $productValue->getAttribute()->willReturn($attribute);
        $this->supportsComparison($productValue)->shouldBe(false);
    }

    function it_returns_null_if_reference_data_is_null(ProductValueInterface $productValue)
    {
        $submittedData = ['color' => 12];

        $attribute = new Attribute();
        $attribute->setReferenceDataName(null);
        $productValue->getAttribute()->willReturn($attribute);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }

    function it_returns_null_if_reference_data_is_not_submitted(ProductValueInterface $productValue)
    {
        $submittedData = [];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('color');
        $productValue->getAttribute()->willReturn($attribute);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }

    function it_returns_null_if_reference_data_submitted_is_empty(ProductValueInterface $productValue)
    {
        $submittedData = ['color' => ''];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('color');
        $productValue->getAttribute()->willReturn($attribute);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }

    function it_changes_with_new_value(CustomProductValue $productValue, ReferenceDataInterface $color)
    {
        $submittedData = ['color' => 12];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('color');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getColor()->willReturn($color);

        $this->getChanges($productValue, $submittedData)->shouldReturn(['color' => 12]);
    }

    function it_changes_with_updated_value(CustomProductValue $productValue, ReferenceDataInterface $color)
    {
        $submittedData = ['color' => 12];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('color');
        $productValue->getAttribute()->willReturn($attribute);
        $color->getId()->willReturn(10);
        $productValue->getColor()->willReturn($color);

        $this->getChanges($productValue, $submittedData)->shouldReturn(['color' => 12]);
    }

    function it_does_not_change_same_values(CustomProductValue $productValue, ReferenceDataInterface $color)
    {
        $submittedData = ['color' => 12];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('color');
        $productValue->getAttribute()->willReturn($attribute);
        $color->getId()->willReturn(12);
        $productValue->getColor()->willReturn($color);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }
}

interface CustomProductValue extends ProductValueInterface
{
    public function getColor();
}
