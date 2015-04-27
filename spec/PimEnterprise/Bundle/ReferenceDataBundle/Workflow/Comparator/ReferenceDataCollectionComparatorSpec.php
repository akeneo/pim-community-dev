<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Comparator;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ReferenceDataCollectionComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_a_multi_reference_data(ProductValueInterface $productValue)
    {
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_multiselect');

        $productValue->getAttribute()->willReturn($attribute);
        $this->supportsComparison($productValue)->shouldBe(true);
    }

    function it_does_not_support_a_non_multi_reference_data(ProductValueInterface $productValue)
    {
        $attribute = new Attribute();
        $attribute->setAttributeType('pim_reference_data_simpleselect');

        $productValue->getAttribute()->willReturn($attribute);
        $this->supportsComparison($productValue)->shouldBe(false);

        $attribute->setAttributeType('other');

        $productValue->getAttribute()->willReturn($attribute);
        $this->supportsComparison($productValue)->shouldBe(false);
    }

    function it_returns_null_if_reference_data_is_null(ProductValueInterface $productValue)
    {
        $submittedData = ['fabrics' => []];

        $attribute = new Attribute();
        $attribute->setReferenceDataName(null);
        $productValue->getAttribute()->willReturn($attribute);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }

    function it_returns_null_if_reference_data_is_not_submitted(ProductValueInterface $productValue)
    {
        $submittedData = [];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('fabrics');
        $productValue->getAttribute()->willReturn($attribute);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }

    function it_returns_null_if_reference_data_submitted_is_empty(ProductValueInterface $productValue)
    {
        $submittedData = ['fabrics' => []];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('fabrics');
        $productValue->getAttribute()->willReturn($attribute);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }

    function it_changes_with_new_value(CustomProductValueCollection $productValue)
    {
        $submittedData = ['fabrics' => '10,11'];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('fabrics');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getFabrics()->willReturn(new ArrayCollection());

        $this->getChanges($productValue, $submittedData)->shouldReturn(['fabrics' => '10,11']);
    }

    function it_changes_with_updated_value(
        CustomProductValueCollection $productValue,
        ReferenceDataInterface $kevlar,
        ReferenceDataInterface $neoprene
    ) {
        $submittedData = ['fabrics' => '11,12'];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('fabrics');
        $productValue->getAttribute()->willReturn($attribute);

        $kevlar->getId()->willReturn(10);
        $neoprene->getId()->willReturn(11);
        $fabrics = new ArrayCollection([
            $neoprene->getWrappedObject(),
            $kevlar->getWrappedObject(),
        ]);
        $productValue->getFabrics()->willReturn($fabrics);

        $this->getChanges($productValue, $submittedData)->shouldReturn(['fabrics' => '11,12']);
    }

    function it_does_not_change_same_values(
        CustomProductValueCollection $productValue,
        ReferenceDataInterface $neoprene
    ) {
        $submittedData = ['fabrics' => '12'];

        $attribute = new Attribute();
        $attribute->setReferenceDataName('fabrics');
        $productValue->getAttribute()->willReturn($attribute);

        $neoprene->getId()->willReturn(12);
        $fabrics = new ArrayCollection([$neoprene->getWrappedObject()]);
        $productValue->getFabrics()->willReturn($fabrics);

        $this->getChanges($productValue, $submittedData)->shouldReturn(null);
    }
}

interface CustomProductValueCollection extends ProductValueInterface
{
    public function getFabrics();
}
