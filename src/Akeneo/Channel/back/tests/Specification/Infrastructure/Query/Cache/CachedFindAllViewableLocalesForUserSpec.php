<?php


namespace Specification\Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use PhpSpec\ObjectBehavior;

class CachedFindAllViewableLocalesForUserSpec extends ObjectBehavior
{
    public function let(FindAllViewableLocalesForUser    $findAllViewableLocalesForUser)
    {
        $this->beConstructedWith($findAllViewableLocalesForUser);
    }

    public function it_finds_all_viewable_locale_for_user_and_caches_it(
        FindAllViewableLocalesForUser $findAllViewableLocalesForUser
    ) {
        $findAllViewableLocalesForUser
            ->findAll(1)
            ->willReturn([
                new Locale('en_US', true),
            ])
            ->shouldBeCalledOnce()
        ;

        $findAllViewableLocalesForUser
            ->findAll(2)
            ->willReturn([
                new Locale('en_US', true),
            ])
            ->shouldBeCalledOnce()
        ;

        $this->findAll(1);
        $this->findAll(1);
        $this->findAll(1);
        $this->findAll(2);
        $this->findAll(2);
    }
}
