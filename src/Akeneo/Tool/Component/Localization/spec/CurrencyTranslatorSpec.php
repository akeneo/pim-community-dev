<?php

namespace spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\CurrencyTranslator;
use PhpSpec\ObjectBehavior;

class CurrencyTranslatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CurrencyTranslator::class);
    }

    function it_translates_currencies()
    {
        $this->translate('EUR', 'fr_FR', 'euros')->shouldReturn('euro');
        $this->translate('EUR', 'en_US', 'euros')->shouldReturn('Euro');
        $this->translate('DKK', 'fr_FR', 'danish crown')->shouldReturn('couronne danoise');
        $this->translate('DKK', 'en_US', 'DKK')->shouldReturn('Danish Krone');
    }

    function it_returns_fallback_when_not_found()
    {
        $this->translate('UNKNOWN', 'fr_FR', 'devise inconnue')->shouldReturn('devise inconnue');
        $this->translate('EUR', 'some_LOCALE', 'pays inconnu')->shouldReturn('pays inconnu');
    }
}
