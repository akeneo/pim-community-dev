<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface;
use PhpSpec\ObjectBehavior;

class ReferenceDataValueSpec extends ObjectBehavior
{
    function it_returns_data() {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $this->getData()->shouldReturn('ref_data');
    }

    function it_returns_data_as_string() {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $this->__toString()->shouldReturn('[ref_data]');
    }

    function it_returns_null_data_as_string()
    {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', null, 'ecommerce', 'en_US']);

        $this->__toString()->shouldReturn('');
    }

    function it_compares_itself_to_the_same_reference_data(
        ReferenceDataValueInterface $sameRefDataValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $sameRefDataValue->getLocaleCode()->willReturn('en_US');
        $sameRefDataValue->getScopeCode()->willReturn('ecommerce');
        $sameRefDataValue->getData()->willReturn('ref_data');

        $this->isEqual($sameRefDataValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_reference_data_to_a_reference_data_value_with_null_reference_data(
        ReferenceDataValueInterface $sameRefDataValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', null, 'ecommerce', 'en_US']);

        $sameRefDataValue->getLocaleCode()->willReturn('en_US');
        $sameRefDataValue->getScopeCode()->willReturn('ecommerce');
        $sameRefDataValue->getData()->willReturn(null);

        $this->isEqual($sameRefDataValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_reference_data_value_with_null_reference_data(
        ReferenceDataValueInterface $otherRefDataValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $otherRefDataValue->getLocaleCode()->willReturn('en_US');
        $otherRefDataValue->getScopeCode()->willReturn('ecommerce');
        $otherRefDataValue->getData()->willReturn(null);

        $this->isEqual($otherRefDataValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_reference_data_value(
        ReferenceDataValueInterface $differentRefDataValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $differentRefDataValue->getLocaleCode()->willReturn('fr_FR');
        $differentRefDataValue->getScopeCode()->willReturn('ecommerce');

        $this->isEqual($differentRefDataValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_value(
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_reference_data(
        ReferenceDataValueInterface $otherRefDataValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue',['my_ref_data_value', 'ref_data', 'ecommerce', 'en_US']);

        $otherRefDataValue->getLocaleCode()->willReturn('en_US');
        $otherRefDataValue->getScopeCode()->willReturn('ecommerce');
        $otherRefDataValue->getData()->willReturn('other_ref_data');

        $this->isEqual($otherRefDataValue)->shouldReturn(false);
    }
}
