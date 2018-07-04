<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Clearer;

use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Clear unit of work and cached repositories.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CacheClearer implements CacheClearerInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var CachedObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $attributeOptionRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $familyRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $categoryRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $channelRepository;

    /** @var CachedObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param ObjectManager                   $objectManager
     * @param EntityManagerInterface          $entityManager
     * @param CachedObjectRepositoryInterface $attributeRepository
     * @param CachedObjectRepositoryInterface $attributeOptionRepository
     * @param CachedObjectRepositoryInterface $familyRepository
     * @param CachedObjectRepositoryInterface $categoryRepository
     * @param CachedObjectRepositoryInterface $channelRepository
     * @param CachedObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        EntityManagerInterface $entityManager,
        CachedObjectRepositoryInterface $attributeRepository,
        CachedObjectRepositoryInterface $attributeOptionRepository,
        CachedObjectRepositoryInterface $familyRepository,
        CachedObjectRepositoryInterface $categoryRepository,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository
    ) {
        $this->objectManager = $objectManager;
        $this->entityManager = $entityManager;
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->channelRepository = $channelRepository;
        $this->familyRepository = $familyRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Clears unit of work and cached repositories.
     */
    public function clear()
    {
        $this->objectManager->clear();
        $this->entityManager->clear();
        $this->attributeRepository->clear();
        $this->attributeOptionRepository->clear();
        $this->familyRepository->clear();
        $this->categoryRepository->clear();
        $this->channelRepository->clear();
        $this->localeRepository->clear();
    }
}
