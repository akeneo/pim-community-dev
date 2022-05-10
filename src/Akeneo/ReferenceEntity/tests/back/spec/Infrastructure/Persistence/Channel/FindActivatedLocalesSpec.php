<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Channel;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use PhpSpec\ObjectBehavior;

class FindActivatedLocalesSpec extends ObjectBehavior
{
    public function let(FindLocales $findLocales)
    {
        $this->beConstructedWith($findLocales);
    }

    public function it_returns_activated_locale_codes(FindLocales $findLocales)
    {
        $frLocale = new Locale('fr_FR', true);
        $deLocale = new Locale('de_DE', true);

        $findLocales->findAllActivated()->willReturn([$frLocale, $deLocale]);

        $this->findAll()->shouldReturn(['fr_FR', 'de_DE']);
    }

    public function it_returns_an_empty_array_if_no_locale_is_active(FindLocales $findLocales)
    {
        $findLocales->findAllActivated()->willReturn([]);

        $this->findAll()->shouldReturn([]);
    }
}
