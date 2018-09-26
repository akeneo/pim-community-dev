<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\DateValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class DateValueSpec extends ObjectBehavior
{
    function it_returns_data(AttributeInterface $attribute, \DateTime $date)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $date);
        $this->getData()->shouldBeAnInstanceOf(\DateTime::class);
        $this->getData()->shouldReturn($date);
    }

    function it_compares_itself_to_a_date_value_with_same_date(
        AttributeInterface $attribute,
        \DateTime $date,
        DateValueInterface $sameDateValue,
        \DateTime $sameDate,
        \DateTimeZone $timeZone,
        \DateTimeZone $sameTimeZone
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $date);

        $date->getTimezone()->willReturn($timeZone);
        $date->getTimestamp()->willReturn(123456);
        $timeZone->getName()->willReturn('timezone');

        $sameDateValue->getLocale()->willReturn('en_US');
        $sameDateValue->getScope()->willReturn('ecommerce');

        $sameDateValue->getData()->willReturn($sameDate);
        $sameDate->getTimezone()->willReturn($sameTimeZone);
        $sameDate->getTimestamp()->willReturn(123456);
        $sameTimeZone->getName()->willReturn('timezone');

        $this->isEqual($sameDateValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_date_to_a_same_date_value_with_null_date(
        AttributeInterface $attribute,
        DateValueInterface $differentDateValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $differentDateValue->getLocale()->willReturn('en_US');
        $differentDateValue->getScope()->willReturn('ecommerce');

        $differentDateValue->getData()->willReturn(null);

        $this->isEqual($differentDateValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_date_to_a_different_date_value_with_null_date(
        AttributeInterface $attribute,
        DateValueInterface $differentDateValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $differentDateValue->getLocale()->willReturn('fr_FR');
        $differentDateValue->getScope()->willReturn('mobile');

        $differentDateValue->getData()->willReturn(null);

        $this->isEqual($differentDateValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_value(
        AttributeInterface $attribute,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_date_value(
        AttributeInterface $attribute,
        \DateTime $date,
        DateValueInterface $differentDateValue,
        \DateTime $differentDate,
        \DateTimeZone $timeZone,
        \DateTimeZone $sameTimeZone
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $date);

        $date->getTimezone()->willReturn($timeZone);
        $date->getTimestamp()->willReturn(123456);
        $timeZone->getName()->willReturn('timezone');

        $differentDateValue->getLocale()->willReturn('en_US');
        $differentDateValue->getScope()->willReturn('ecommerce');

        $differentDateValue->getData()->willReturn($differentDate);
        $differentDate->getTimezone()->willReturn($sameTimeZone);
        $differentDate->getTimestamp()->willReturn(654321);
        $sameTimeZone->getName()->willReturn('timezone');

        $this->isEqual($differentDateValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_date_value_with_null_date(
        AttributeInterface $attribute,
        \DateTime $date,
        DateValueInterface $differentDateValue,
        \DateTimeZone $timeZone
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $date);

        $date->getTimezone()->willReturn($timeZone);
        $date->getTimestamp()->willReturn(123456);
        $timeZone->getName()->willReturn('timezone');

        $differentDateValue->getLocale()->willReturn('en_US');
        $differentDateValue->getScope()->willReturn('ecommerce');

        $differentDateValue->getData()->willReturn(null);

        $this->isEqual($differentDateValue)->shouldReturn(false);
    }
}
