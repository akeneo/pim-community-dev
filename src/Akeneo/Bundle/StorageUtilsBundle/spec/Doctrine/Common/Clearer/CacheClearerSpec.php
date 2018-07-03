<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Clearer;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Clearer\CacheClearer;
use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

class CacheClearerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EntityManagerInterface $entityManager,
        CachedObjectRepositoryInterface $attributeRepository,
        CachedObjectRepositoryInterface $attributeOptionRepository,
        CachedObjectRepositoryInterface $familyRepository,
        CachedObjectRepositoryInterface $categoryRepository,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith(
            $objectManager,
            $entityManager,
            $attributeRepository,
            $attributeOptionRepository,
            $familyRepository,
            $categoryRepository,
            $channelRepository,
            $localeRepository
        );
    }

    function it_is_a_cache_clearer()
    {
        $this->shouldHaveType(CacheClearer::class);
        $this->shouldImplement(CacheClearerInterface::class);
    }

    function it_clears_the_uow_and_cached_repositories(
        $objectManager,
        $entityManager,
        $attributeRepository,
        $attributeOptionRepository,
        $familyRepository,
        $categoryRepository,
        $channelRepository,
        $localeRepository
    ) {
        $objectManager->clear()->shouldBeCalled();
        $entityManager->clear()->shouldBeCalled();
        $attributeRepository->clear()->shouldBeCalled();
        $attributeOptionRepository->clear()->shouldBeCalled();
        $familyRepository->clear()->shouldBeCalled();
        $categoryRepository->clear()->shouldBeCalled();
        $channelRepository->clear()->shouldBeCalled();
        $localeRepository->clear()->shouldBeCalled();

        $this->clear();
    }
}
