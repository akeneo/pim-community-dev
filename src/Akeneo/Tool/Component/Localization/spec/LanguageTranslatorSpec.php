<?php

namespace spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\LanguageTranslator;
use PhpSpec\ObjectBehavior;

class LanguageTranslatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(LanguageTranslator::class);
    }

    function it_translates_languages()
    {
        $this->translate('fr_FR', 'fr', '[français]')->shouldReturn('français France');
        $this->translate('en_US', 'en', '[english]')->shouldReturn('English United States');
        $this->translate('en_GB', 'en', '[english]')->shouldReturn('English United Kingdom');
        $this->translate('en_GB', 'de', '[english]')->shouldReturn('Englisch Vereinigtes Königreich');
    }

    function it_returns_fallback_when_not_found()
    {
        $this->translate('en_GB', 'unknown', '[this is unknown]')->shouldReturn('[this is unknown]');
        $this->translate('UNKNOWN_FR', 'fr', '[unknown language]')->shouldReturn('[unknown language]');
    }

    function it_returns_fallback_when_intl_can_not_translate_the_country_name_into_the_given_locale()
    {
        $this->translate('bs_Cyrl_BA', 'en_US', '[bs_Cyrl_BA]')->shouldReturn('[bs_Cyrl_BA]');
    }
}
