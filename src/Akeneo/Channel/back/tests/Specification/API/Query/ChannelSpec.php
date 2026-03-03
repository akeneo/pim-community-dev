<?php

namespace Specification\Akeneo\Channel\API\Query;

use Akeneo\Channel\API\Query\ConversionUnitCollection;
use Akeneo\Channel\API\Query\LabelCollection;
use PhpSpec\ObjectBehavior;

class ChannelSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'mobile',
            ['fr_FR', 'uk_UA'],
            LabelCollection::fromArray(['fr_FR' => 'Mobile', 'uk_UA' => 'смартфон']),
            ['EUR', 'USD'],
            ConversionUnitCollection::fromArray(['a_measurement_attribute' => 'GRAM', 'another_measurement_attribute' => 'POUND'])
        );
    }

    public function it_has_getters()
    {
        $this->getCode()->shouldReturn('mobile');
        $this->getLocaleCodes()->shouldReturn(['fr_FR', 'uk_UA']);
        $this->getLabels()->shouldBeLike(LabelCollection::fromArray(['fr_FR' => 'Mobile', 'uk_UA' => 'смартфон']));
        $this->getActiveCurrencies()->shouldReturn(['EUR', 'USD']);
        $this->getConversionUnits()->shouldBeLike(ConversionUnitCollection::fromArray(['a_measurement_attribute' => 'GRAM', 'another_measurement_attribute' => 'POUND']));
    }

    public function it_tells_if_a_given_locale_is_active()
    {
        $this->isLocaleActive('fr_FR')->shouldReturn(true);
        $this->isLocaleActive('uk_UA')->shouldReturn(true);
        $this->isLocaleActive('en_US')->shouldReturn(false);
    }

    public function it_tells_if_a_given_currency_is_active()
    {
        $this->isCurrencyActive('EUR')->shouldReturn(true);
        $this->isCurrencyActive('USD')->shouldReturn(true);
        $this->isCurrencyActive('GBP')->shouldReturn(false);
    }
}
