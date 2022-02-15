<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Test\Acceptance\InMemory;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Test\Acceptance\Locale\InMemoryLocaleRepository;
use PhpSpec\ObjectBehavior;

class InMemoryGetEditableLocaleCodesSpec extends ObjectBehavior
{
    function let()
    {
        $localeRepository = new InMemoryLocaleRepository();

        $frFRLocale = new Locale();
        $frFRLocale->setCode('fr_FR');
        $frFRLocale->addChannel(new Channel());
        $localeRepository->save($frFRLocale);

        $enUSLocale = new Locale();
        $enUSLocale->setCode('en_US');
        $enUSLocale->addChannel(new Channel());
        $localeRepository->save($enUSLocale);

        $deDELocale = new Locale();
        $deDELocale->setCode('de_DE');
        $localeRepository->save($deDELocale);

        $this->beConstructedWith($localeRepository);
    }

    function it_returns_all_activated_locale_codes()
    {
        $this->forUserId(10)->shouldReturn(['fr_FR', 'en_US']);
        $this->forUserId(20)->shouldReturn(['fr_FR', 'en_US']);
    }
}
