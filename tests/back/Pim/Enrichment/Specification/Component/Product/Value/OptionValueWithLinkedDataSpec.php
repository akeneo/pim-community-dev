<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use PhpSpec\ObjectBehavior;

class OptionValueWithLinkedDataSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->beConstructedWith('size', 'xs', null,null, [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);

        $this->shouldBeAnInstanceOf(OptionValueWithLinkedData::class);
    }

    function it_can_be_formatted_as_string()
    {
        $this->beConstructedWith('size', 'xs', null,null, [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);
        $this->__toString()->shouldReturn('[xs]');
    }

    function it_returns_data()
    {
        $this->beConstructedWith('size', 'xs', null,null, [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);

        $this->getData()->shouldReturn('xs');
    }

    function it_returns_linked_data()
    {
        $this->beConstructedWith('size', 'xs', null,null, [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);

        $this->getLinkedData()->shouldReturn(['attribute' => 'size', 'code' => 'xs', 'labels' => ['en_US' => 'XS', 'fr_FR' => 'XS',]]);
    }

    function it_compares_itself_to_the_same_option_value(OptionValueWithLinkedData $sameOptionValue)
    {
        $this->beConstructedWith('size', 'xs', 'ecommerce','en_US', [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn('xs');
        $sameOptionValue->getLinkedData()->willReturn(['attribute' => 'size', 'code' => 'xs', 'labels' => ['en_US' => 'XS', 'fr_FR' => 'XS',]]);

        $this->isEqual($sameOptionValue)->shouldReturn(true);
    }

    function it_compares_itself_to_another_value_type(MetricValueInterface $metricValue)
    {
        $this->beConstructedWith('size', 'xs', 'ecommerce','en_US', [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_with_null_option_to_an_option_value_with_null_option(OptionValueWithLinkedData $sameOptionValue)
    {
        $this->beConstructedWith('size', null, 'ecommerce','en_US', null);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn(null);
        $sameOptionValue->getLinkedData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(true);
    }

    function it_compares_itself_to_an_option_value_with_null_option(OptionValueWithLinkedData $sameOptionValue)
    {
        $this->beConstructedWith('size', 'xs', 'ecommerce','en_US', null);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn(null);
        $sameOptionValue->getLinkedData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_an_option_value_with_different_option(OptionValueWithLinkedData $sameOptionValue)
    {
        $this->beConstructedWith('size', 'xs', 'ecommerce','en_US', null);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn('xl');
        $sameOptionValue->getLinkedData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_option_value(OptionValueWithLinkedData $sameOptionValue)
    {
        $this->beConstructedWith('size', 'xs', 'ecommerce','en_US', null);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('mobile');
        $sameOptionValue->getData()->willReturn('xs');
        $sameOptionValue->getLinkedData()->willReturn(null);

        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_linked_data(OptionValueWithLinkedData $sameOptionValue)
    {
        $this->beConstructedWith('size', 'xs', 'ecommerce','en_US', [
            'attribute' => 'size',
            'code' => 'xs',
            'labels' => [
                'en_US' => 'XS',
                'fr_FR' => 'XS',
            ]
        ]);

        $sameOptionValue->getLocaleCode()->willReturn('en_US');
        $sameOptionValue->getScopeCode()->willReturn('ecommerce');
        $sameOptionValue->getData()->willReturn('xs');
        $sameOptionValue->getLinkedData()->willReturn(['attribute' => 'size', 'code' => 'xl', 'labels' => ['en_US' => 'XS', 'fr_FR' => 'XS',]]);


        $this->isEqual($sameOptionValue)->shouldReturn(false);
    }
}

