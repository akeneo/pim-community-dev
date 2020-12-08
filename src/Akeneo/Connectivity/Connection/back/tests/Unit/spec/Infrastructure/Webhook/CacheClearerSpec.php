<?php
declare(strict_types=1);

namespace spec\AkeneoEnterprise\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\LocaleRight\GetAllViewableLocalesForUser;
use PhpSpec\ObjectBehavior;

class CacheClearerSpec extends ObjectBehavior
{
    public function let(
        CacheClearerInterface $communityCacheClearer,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser
    ): void {
        $this->beConstructedWith($communityCacheClearer, $getAllViewableLocalesForUser);
    }

    public function it_clears_the_cache($communityCacheClearer, $getAllViewableLocalesForUser): void
    {
        $communityCacheClearer->clear()->shouldBeCalled();
        $getAllViewableLocalesForUser->clearCache()->shouldBeCalled();

        $this->clear();
    }
}
