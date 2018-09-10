<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\DateValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use PhpSpec\ObjectBehavior;

class DateValueSpec extends ObjectBehavior
{
    function it_returns_data(\DateTime $date)
    {
        $this->beConstructedThrough('value', ['my_date', $date]);

        $this->getData()->shouldBeAnInstanceOf(\DateTime::class);
        $this->getData()->shouldReturn($date);
    }

    function it_compares_itself_to_a_date_value_with_same_date(
        \DateTime $date,
        DateValueInterface $sameDateValue,
        \DateTime $sameDate,
        \DateTimeZone $timeZone,
        \DateTimeZone $sameTimeZone
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_date', $date, 'ecommerce', 'en_US']);

        $date->getTimezone()->willReturn($timeZone);
        $date->getTimestamp()->willReturn(123456);
        $timeZone->getName()->willReturn('timezone');

        $sameDateValue->getLocaleCode()->willReturn('en_US');
        $sameDateValue->getScopeCode()->willReturn('ecommerce');

        $sameDateValue->getData()->willReturn($sameDate);
        $sameDate->getTimezone()->willReturn($sameTimeZone);
        $sameDate->getTimestamp()->willReturn(123456);
        $sameTimeZone->getName()->willReturn('timezone');

        $this->isEqual($sameDateValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_date_to_a_same_date_value_with_null_date(
        DateValueInterface $differentDateValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_date', null, 'ecommerce', 'en_US']);

        $differentDateValue->getLocaleCode()->willReturn('en_US');
        $differentDateValue->getScopeCode()->willReturn('ecommerce');

        $differentDateValue->getData()->willReturn(null);

        $this->isEqual($differentDateValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_date_to_a_different_date_value_with_null_date(
        DateValueInterface $differentDateValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_date', null, 'ecommerce', 'en_US']);

        $differentDateValue->getLocaleCode()->willReturn('fr_FR');
        $differentDateValue->getScopeCode()->willReturn('mobile');

        $differentDateValue->getData()->willReturn(null);

        $this->isEqual($differentDateValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_value(
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_date', null, 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_date_value(
        \DateTime $date,
        DateValueInterface $differentDateValue,
        \DateTime $differentDate,
        \DateTimeZone $timeZone,
        \DateTimeZone $sameTimeZone
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_date', $date, 'ecommerce', 'en_US']);

        $date->getTimezone()->willReturn($timeZone);
        $date->getTimestamp()->willReturn(123456);
        $timeZone->getName()->willReturn('timezone');

        $differentDateValue->getLocaleCode()->willReturn('en_US');
        $differentDateValue->getScopeCode()->willReturn('ecommerce');

        $differentDateValue->getData()->willReturn($differentDate);
        $differentDate->getTimezone()->willReturn($sameTimeZone);
        $differentDate->getTimestamp()->willReturn(654321);
        $sameTimeZone->getName()->willReturn('timezone');

        $this->isEqual($differentDateValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_date_value_with_null_date(
        \DateTime $date,
        DateValueInterface $differentDateValue,
        \DateTimeZone $timeZone
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_date', $date, 'ecommerce', 'en_US']);

        $date->getTimezone()->willReturn($timeZone);
        $date->getTimestamp()->willReturn(123456);
        $timeZone->getName()->willReturn('timezone');

        $differentDateValue->getLocaleCode()->willReturn('en_US');
        $differentDateValue->getScopeCode()->willReturn('ecommerce');

        $differentDateValue->getData()->willReturn(null);

        $this->isEqual($differentDateValue)->shouldReturn(false);
    }
}
