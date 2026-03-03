<?php


namespace Specification\Akeneo\Channel\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use PhpSpec\ObjectBehavior;

class CachedFindAllEditableLocalesForUserSpec extends ObjectBehavior
{
    public function let(FindAllEditableLocalesForUser    $findAllEditableLocalesForUser)
    {
        $this->beConstructedWith($findAllEditableLocalesForUser);
    }

    public function it_finds_all_editable_locale_for_user_and_caches_it(
        FindAllEditableLocalesForUser $findAllEditableLocalesForUser
    ) {
        $findAllEditableLocalesForUser
            ->findAll(1)
            ->willReturn([
                new Locale('en_US', true),
            ])
            ->shouldBeCalledOnce()
        ;

        $findAllEditableLocalesForUser
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
