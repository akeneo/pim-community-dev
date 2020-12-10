<?php
declare(strict_types=1);

namespace spec\AkeneoEnterprise\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\Cache\LRUCachedGetViewableAttributeCodesForUser;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\LocaleRight\GetAllViewableLocalesForUser;
use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use PhpSpec\ObjectBehavior;

class CacheClearerSpec extends ObjectBehavior
{
    public function let(
        CacheClearerInterface $communityCacheClearer,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ): void {
        $LRUCachedGetViewableAttributeCodesForUser = new LRUCachedGetViewableAttributeCodesForUser(
            $getViewableAttributeCodesForUser->getWrappedObject()
        );
        $this->beConstructedWith(
            $communityCacheClearer,
            $getAllViewableLocalesForUser,
            $LRUCachedGetViewableAttributeCodesForUser
        );
    }

    public function it_clears_the_cache(
        $communityCacheClearer,
        $getAllViewableLocalesForUser,
        $LRUCachedGetViewableAttributeCodesForUser
    ): void {
        $communityCacheClearer->clear()->shouldBeCalled();
        $getAllViewableLocalesForUser->clearCache()->shouldBeCalled();

        $this->clear();
    }
}
