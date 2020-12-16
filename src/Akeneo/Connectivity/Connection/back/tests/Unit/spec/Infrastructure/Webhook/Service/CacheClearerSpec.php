<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Channel\Bundle\Doctrine\Query\FindActivatedCurrencies;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\CacheClearer;
use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueriesClearer;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueriesClearerInterface;
use PhpSpec\ObjectBehavior;

class CacheClearerSpec extends ObjectBehavior
{
    public function let(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        FindActivatedCurrencies $findActivatedCurrencies,
        UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer,
        GetAttributes $getAttributes,
        CachedQueriesClearerInterface $cachedQueriesClearer
    ): void {
        $LRUCachedGetAttributes = new LRUCachedGetAttributes($getAttributes->getWrappedObject());
        $this->beConstructedWith(
            $channelExistsWithLocale,
            $findActivatedCurrencies,
            $unitOfWorkAndRepositoriesClearer,
            $LRUCachedGetAttributes,
            $cachedQueriesClearer
        );
    }

    public function it_is_a_cache_clearer(): void
    {
        $this->shouldHaveType(CacheClearer::class);
        $this->shouldImplement(CacheClearerInterface::class);
    }

    public function it_clears_the_cache(
        $channelExistsWithLocale,
        $findActivatedCurrencies,
        $unitOfWorkAndRepositoriesClearer,
        CachedQueriesClearerInterface $cachedQueriesClearer
    ): void {
        $channelExistsWithLocale->clearCache()->shouldBeCalled();
        $findActivatedCurrencies->clearCache()->shouldBeCalled();
        $unitOfWorkAndRepositoriesClearer->clear()->shouldBeCalled();
        $cachedQueriesClearer->clear()->shouldBeCalled();

        $this->clear();
    }
}
