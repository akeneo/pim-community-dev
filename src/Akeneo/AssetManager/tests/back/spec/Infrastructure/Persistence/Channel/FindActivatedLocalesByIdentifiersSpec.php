<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Channel;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use PhpSpec\ObjectBehavior;

class FindActivatedLocalesByIdentifiersSpec extends ObjectBehavior
{
    public function let(FindLocales $findLocales)
    {
        $frLocale = new Locale('fr_FR', true);
        $deLocale = new Locale('de_DE', true);

        $findLocales->findAllActivated()->willReturn([$frLocale, $deLocale]);

        $this->beConstructedWith($findLocales);
    }

    public function it_fetches_activated_locale_codes_from_identifiers()
    {
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'en_US']);
        $expectedIdentifiers = LocaleIdentifierCollection::fromNormalized(['fr_FR']);

        $result = $this->find($localeIdentifiers);

        $result->shouldBeLike($expectedIdentifiers);
    }

    public function it_is_case_insensitive()
    {
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized(['FR_fR', 'de_dE']);
        $expectedIdentifiers = LocaleIdentifierCollection::fromNormalized(['fr_FR', 'de_DE']);

        $this->find($localeIdentifiers)->shouldBeLike($expectedIdentifiers);
    }
}
