<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\CacheClearer;
use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueriesClearerInterface;
use PhpSpec\ObjectBehavior;

class CacheClearerSpec extends ObjectBehavior
{
    public function let(
        UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer,
        CachedQueriesClearerInterface $cachedQueriesClearer
    ): void {
        $this->beConstructedWith($unitOfWorkAndRepositoriesClearer, $cachedQueriesClearer);
    }

    public function it_is_a_cache_clearer(): void
    {
        $this->shouldHaveType(CacheClearer::class);
        $this->shouldImplement(CacheClearerInterface::class);
    }

    public function it_clears_the_cache(
        $unitOfWorkAndRepositoriesClearer,
        CachedQueriesClearerInterface $cachedQueriesClearer
    ): void {
        $unitOfWorkAndRepositoriesClearer->clear()->shouldBeCalled();
        $cachedQueriesClearer->clear()->shouldBeCalled();

        $this->clear();
    }
}
