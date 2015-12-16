<?php

namespace spec\Pim\Component\Localization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Factory\DateFactory;
use Pim\Component\Localization\Factory\NumberFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleResolverSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        DateFactory $dateFactory,
        NumberFactory $numberFactory
    ) {
        $this->beConstructedWith($requestStack, $dateFactory, $numberFactory, 'en');
    }

    function it_returns_options_with_a_request(
        $requestStack,
        $dateFactory,
        $numberFactory,
        Request $request,
        \IntlDateFormatter $dateFormatter,
        \NumberFormatter $numberFormatter
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $request->getLocale()->willReturn('en');

        $options = ['locale' => 'en'];
        $numberFactory->create($options)->willReturn($numberFormatter);
        $numberFormatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL)->willReturn('.');
        $dateFactory->create($options)->willReturn($dateFormatter);
        $dateFormatter->getPattern()->willReturn('yyyy-MM-dd');

        $this->getFormats()->shouldReturn(
            [
                'decimal_separator' => '.',
                'date_format'       => 'yyyy-MM-dd'
            ]
        );

        $dateFactory->create(['locale' => 'en', 'timetype' => \IntlDateFormatter::SHORT])->willReturn($dateFormatter);
        $dateFormatter->getPattern()->willReturn('yyyy-MM-dd hh:mm');

        $this->getFormats()->shouldReturn(
            [
                'decimal_separator' => '.',
                'date_format'       => 'yyyy-MM-dd hh:mm'
            ]
        );
    }

    function it_returns_options_with_nullable_request(
        $requestStack,
        $dateFactory,
        $numberFactory,
        \IntlDateFormatter $dateFormatter,
        \NumberFormatter $numberFormatter
    ) {
        $requestStack->getCurrentRequest()->willReturn(null);

        $options = ['locale' => 'en'];
        $numberFactory->create($options)->willReturn($numberFormatter);
        $numberFormatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL)->willReturn('.');
        $dateFactory->create(['locale' => 'en'])->willReturn($dateFormatter);
        $dateFormatter->getPattern()->willReturn('yyyy-MM-dd');

        $this->getFormats()->shouldReturn(
            [
                'decimal_separator' => '.',
                'date_format'       => 'yyyy-MM-dd'
            ]
        );
    }
}
