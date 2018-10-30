<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ReferenceDataValueSpec extends ObjectBehavior
{
    function it_returns_data(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $this->getData()->shouldReturn($referenceData);
    }

    function it_returns_data_as_string(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $referenceData->__toString()->willReturn('ref_data');

        $this->__toString()->shouldReturn('ref_data');
    }

    function it_returns_null_data_as_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $this->__toString()->shouldReturn('');
    }

    function it_compares_itself_to_the_same_reference_data(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData,
        ReferenceDataInterface $sameReferenceData,
        ReferenceDataValueInterface $sameRefDataValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $sameRefDataValue->getLocale()->willReturn('en_US');
        $sameRefDataValue->getScope()->willReturn('ecommerce');
        $sameRefDataValue->getData()->willReturn($sameReferenceData);

        $sameReferenceData->getCode()->willReturn('ref_data');
        $referenceData->getCode()->willReturn('ref_data');

        $this->isEqual($sameRefDataValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_reference_data_to_a_reference_data_value_with_null_reference_data(
        AttributeInterface $attribute,
        ReferenceDataValueInterface $sameRefDataValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameRefDataValue->getLocale()->willReturn('en_US');
        $sameRefDataValue->getScope()->willReturn('ecommerce');
        $sameRefDataValue->getData()->willReturn(null);

        $this->isEqual($sameRefDataValue)->shouldReturn(true);
    }

    function it_compares_itself_to_a_reference_data_value_with_null_reference_data(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData,
        ReferenceDataValueInterface $sameRefDataValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $sameRefDataValue->getLocale()->willReturn('en_US');
        $sameRefDataValue->getScope()->willReturn('ecommerce');
        $sameRefDataValue->getData()->willReturn(null);

        $this->isEqual($sameRefDataValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_reference_data_value(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData,
        ReferenceDataValueInterface $differentRefDataValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $differentRefDataValue->getLocale()->willReturn('fr_FR');
        $differentRefDataValue->getScope()->willReturn('ecommerce');

        $this->isEqual($differentRefDataValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_value(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_reference_data(
        AttributeInterface $attribute,
        ReferenceDataInterface $referenceData,
        ReferenceDataInterface $differentReferenceData,
        ReferenceDataValueInterface $sameRefDataValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $referenceData);

        $sameRefDataValue->getLocale()->willReturn('en_US');
        $sameRefDataValue->getScope()->willReturn('ecommerce');
        $sameRefDataValue->getData()->willReturn($differentReferenceData);

        $differentReferenceData->getCode()->willReturn('different_reference_data');
        $referenceData->getCode()->willReturn('ref_data');

        $this->isEqual($sameRefDataValue)->shouldReturn(false);
    }
}
