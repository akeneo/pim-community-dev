<?php

namespace spec\Pim\Component\Localization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Factory\DateFactory;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleResolverSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        DateFactory $dateFactory,
        FormatProviderInterface $numberFormatProvider
    ) {
        $this->beConstructedWith($requestStack, $dateFactory, $numberFormatProvider, 'en');
    }

    function it_returns_options_with_a_request(
        $requestStack,
        $dateFactory,
        $numberFormatProvider,
        Request $request,
        \IntlDateFormatter $dateFormatter
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $request->getLocale()->willReturn('en');

        $numberFormatProvider->getFormat('en')->willReturn(['decimal_separator' => '.']);
        $dateFactory->create(['locale' => 'en'])->willReturn($dateFormatter);
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
        $numberFormatProvider,
        \IntlDateFormatter $dateFormatter
    ) {
        $requestStack->getCurrentRequest()->willReturn(null);

        $numberFormatProvider->getFormat('en')->willReturn(['decimal_separator' => '.']);
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
