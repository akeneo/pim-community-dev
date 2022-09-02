<?php


namespace Specification\Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use PhpSpec\ObjectBehavior;

class CachedFindLocalesSpec extends ObjectBehavior
{
    public function let(FindLocales $findLocales)
    {
        $this->beConstructedWith($findLocales);
    }

    public function it_finds_a_locale_by_its_code_and_caches_it(
        FindLocales $findLocales
    ) {
        $findLocales
            ->find('en_US')
            ->willReturn(new Locale('en_US', true))
            ->shouldBeCalledOnce()
        ;

        $this->find('en_US');
        $this->find('en_US');
        $this->find('en_US');
    }

    public function it_finds_all_activated_locales_and_caches_them(
        FindLocales $findLocales
    ) {
        $findLocales
            ->findAllActivated()
            ->willReturn([
                new Locale('en_US', true),
                new Locale('fr_FR', true),
            ])
            ->shouldBeCalledOnce();

        $this->findAllActivated();
        $this->findAllActivated();
        $this->findAllActivated();
    }
}
