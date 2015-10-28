<?php

namespace spec\Pim\Component\Localization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleResolverSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        FormatProviderInterface $dateFormatProvider,
        FormatProviderInterface $numberFormatProvider
    ) {
        $this->beConstructedWith($requestStack, $dateFormatProvider, $numberFormatProvider, 'en');
    }

    function it_returns_options_with_a_request(
        $requestStack,
        $dateFormatProvider,
        $numberFormatProvider,
        Request $request
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $request->getLocale()->willReturn('en');

        $numberFormatProvider->getFormat('en')->willReturn(['decimal_separator' => '.']);
        $dateFormatProvider->getFormat('en')->willReturn('Y-m-d');

        $this->getFormats()->shouldReturn(
            [
                'decimal_separator' => '.',
                'date_format'       => 'Y-m-d'
            ]
        );
    }

    function it_returns_options_with_nullable_request($requestStack, $dateFormatProvider, $numberFormatProvider)
    {
        $requestStack->getCurrentRequest()->willReturn(null);

        $numberFormatProvider->getFormat('en')->willReturn(['decimal_separator' => '.']);
        $dateFormatProvider->getFormat('en')->willReturn('Y-m-d');

        $this->getFormats()->shouldReturn(
            [
                'decimal_separator' => '.',
                'date_format'       => 'Y-m-d'
            ]
        );
    }
}
