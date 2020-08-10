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
        $this->translate('fr_FR', 'fr', '[français]')->shouldReturn('français');
        $this->translate('en_US', 'en', '[english]')->shouldReturn('English');
        $this->translate('en_GB', 'en', '[english]')->shouldReturn('English');
    }

    function it_returns_fallback_when_not_found()
    {
        $this->translate('UNKNOWN_FR', 'fr', 'unknown language')->shouldReturn('unknown language');
    }
}
