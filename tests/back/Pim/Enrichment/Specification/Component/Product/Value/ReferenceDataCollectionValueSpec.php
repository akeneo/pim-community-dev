<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use PhpSpec\ObjectBehavior;

class ReferenceDataCollectionValueSpec extends ObjectBehavior
{
    function it_returns_data() {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1', 'ref_data_2'], 'ecommerce', 'en_US']);

        $this->getData()->shouldReturn(['ref_data_1', 'ref_data_2']);
    }

    function it_returns_reference_data_codes() {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1', 'ref_data_2'], 'ecommerce', 'en_US']);

        $this->getReferenceDataCodes()->shouldReturn(['ref_data_1', 'ref_data_2']);
    }

    function it_returns_reference_data_as_string() {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1', 'ref_data_2'], 'ecommerce', 'en_US']);

        $this->__toString()->shouldReturn('[ref_data_1], [ref_data_2]');
    }

    function it_returns_null_reference_data_as_string() {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', null, 'ecommerce', 'en_US']);

        $this->__toString()->shouldReturn('');
    }

    function it_compares_itself_to_the_same_reference_data_collection_value(
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1', 'ref_data_2'], 'ecommerce', 'en_US']);

        $refDataCollectionValue->getScopeCode()->willReturn('ecommerce');
        $refDataCollectionValue->getLocaleCode()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn(['ref_data_1', 'ref_data_2']);

        $this->isEqual($refDataCollectionValue)->shouldReturn(true);
    }

    function it_compares_itself_with_empty_collection_to_the_same_empty_reference_data_collection_value(
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', null, 'ecommerce', 'en_US']);

        $refDataCollectionValue->getScopeCode()->willReturn('ecommerce');
        $refDataCollectionValue->getLocaleCode()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn([]);

        $this->isEqual($refDataCollectionValue)->shouldReturn(true);
    }

    function it_compares_itself_to_the_same_reference_data_collection_value_but_empty(
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1', 'ref_data_2'], 'ecommerce', 'en_US']);

        $refDataCollectionValue->getScopeCode()->willReturn('ecommerce');
        $refDataCollectionValue->getLocaleCode()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn([]);

        $this->isEqual($refDataCollectionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_reference_data_collection_value(
        ReferenceDataCollectionValueInterface $refDataCollectionValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', null, 'ecommerce', 'en_US']);

        $refDataCollectionValue->getScopeCode()->willReturn('mobile');
        $refDataCollectionValue->getLocaleCode()->willReturn('en_US');

        $this->isEqual($refDataCollectionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_another_value(
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1'], 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_reference_data_collection_value_with_different_collection(
        ReferenceDataCollectionValueInterface $refDataCollectionValue,
        ReferenceDataInterface $sameReferenceData1,
        ReferenceDataInterface $differentReferenceData2
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_ref_data_collection', ['ref_data_1', 'ref_data_2'], 'ecommerce', 'en_US']);
        $refDataCollectionValue->getScopeCode()->willReturn('ecommerce');
        $refDataCollectionValue->getLocaleCode()->willReturn('en_US');
        $refDataCollectionValue->getData()->willReturn(['ref_data_1', 'the_different_ref_data']);

        $this->isEqual($refDataCollectionValue)->shouldReturn(false);
    }
}
