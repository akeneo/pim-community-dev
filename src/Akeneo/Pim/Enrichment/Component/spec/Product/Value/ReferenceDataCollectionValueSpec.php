<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ReferenceDataCollectionValueSpec extends ObjectBehavior
{
    function it_returns_data(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData1,$referenceData2]);

        $this->getData()->shouldReturn([$referenceData1,$referenceData2]);
    }

    function it_returns_reference_data_codes(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData1,$referenceData2]);

        $referenceData1->getCode()->willReturn('ref_data_1');
        $referenceData2->getCode()->willReturn('ref_data_2');

        $this->getReferenceDataCodes()->shouldReturn(['ref_data_1', 'ref_data_2']);
    }

    function it_returns_reference_data_as_string(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData1,$referenceData2]);

        $referenceData1->__toString()->willReturn('ref_data_1');
        $referenceData2->__toString()->willReturn('ref_data_2');

        $this->__toString()->shouldReturn('ref_data_1, ref_data_2');
    }

    function it_returns_null_reference_data_as_string(
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US');

        $this->__toString()->shouldReturn('');
    }

    function it_compares_itself_to_the_same_reference_data_collection_value(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2,
        ReferenceDataCollectionValueInterface $refDataCollectionValue,
        ReferenceDataInterface $sameReferenceData1,
        ReferenceDataInterface $sameReferenceData2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData1,$referenceData2]);

        $refDataCollectionValue->getScope()->willReturn('ecommerce');
        $refDataCollectionValue->getLocale()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn([$sameReferenceData1, $sameReferenceData2]);

        $referenceData1->getCode()->willReturn('ref_data_1');
        $sameReferenceData1->getCode()->willReturn('ref_data_1');

        $referenceData2->getCode()->willReturn('ref_data_2');
        $sameReferenceData2->getCode()->willReturn('ref_data_2');

        $this->isEqual($refDataCollectionValue)->shouldReturn(true);
    }

    function it_compares_itself_with_empty_collection_to_the_same_empty_reference_data_collection_value(
        AttributeInterface $attribute,
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US');

        $refDataCollectionValue->getScope()->willReturn('ecommerce');
        $refDataCollectionValue->getLocale()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn([]);

        $this->isEqual($refDataCollectionValue)->shouldReturn(true);
    }

    function it_compares_itself_to_the_same_reference_data_collection_value_but_empty(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2,
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData1,$referenceData2]);

        $refDataCollectionValue->getScope()->willReturn('ecommerce');
        $refDataCollectionValue->getLocale()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn([]);

        $this->isEqual($refDataCollectionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_reference_data_collection_value(
        AttributeInterface $attribute,
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US');

        $refDataCollectionValue->getScope()->willReturn('mobile');
        $refDataCollectionValue->getLocale()->willReturn('en_US');

        $this->isEqual($refDataCollectionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_another_value(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData]);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_reference_data_collection_value_with_different_collection(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData1,
        ReferenceDataInterface $referenceData2,
        ReferenceDataCollectionValueInterface $refDataCollectionValue,
        ReferenceDataInterface $sameReferenceData1,
        ReferenceDataInterface $differentReferenceData2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', [$referenceData1,$referenceData2]);

        $refDataCollectionValue->getScope()->willReturn('ecommerce');
        $refDataCollectionValue->getLocale()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn([$sameReferenceData1, $differentReferenceData2]);

        $referenceData1->getCode()->willReturn('ref_data_1');
        $sameReferenceData1->getCode()->willReturn('ref_data_1');

        $referenceData2->getCode()->willReturn('ref_data_2');
        $differentReferenceData2->getCode()->willReturn('the_different_ref_data');

        $this->isEqual($refDataCollectionValue)->shouldReturn(false);
    }
}
