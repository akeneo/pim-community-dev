<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\AssetRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\LocaleAccessRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\ProductDraftRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\ProjectRepository;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

/**
 * Collects the structure of the PIM Catalog (Enterprise features):
 * - Proposals count
 * - Projects count
 * - Assets count
 * - Published products count
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class DBDataCollector implements DataCollectorInterface
{
    /** @var ProductDraftRepository */
    protected $draftRepository;

    /** @var ProjectRepository */
    protected $projectRepository;

    /** @var AssetRepository */
    protected $assetRepository;

    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /** @var LocaleAccessRepository */
    protected $localeAccessRepository;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepository;

    /**
     * @param ProductDraftRepository              $draftRepository
     * @param ProjectRepository                   $projectRepository
     * @param AssetRepository                     $assetRepository
     * @param PublishedProductRepositoryInterface $publishedRepository
     * @param LocaleAccessRepository              $localeAccessRepository
     * @param CategoryAccessRepository            $categoryAccessRepository
     */
    public function __construct(
        ProductDraftRepository $draftRepository,
        ProjectRepository $projectRepository,
        AssetRepository $assetRepository,
        PublishedProductRepositoryInterface $publishedRepository,
        LocaleAccessRepository $localeAccessRepository,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->draftRepository = $draftRepository;
        $this->projectRepository = $projectRepository;
        $this->assetRepository = $assetRepository;
        $this->publishedRepository = $publishedRepository;
        $this->localeAccessRepository = $localeAccessRepository;
        $this->categoryAccessRepository = $categoryAccessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_product_drafts'           => $this->draftRepository->countAll(),
            'nb_projects'                 => $this->projectRepository->countAll(),
            'nb_assets'                   => $this->assetRepository->countAll(),
            'nb_published_products'       => $this->publishedRepository->countAll(),
            'nb_custom_locale_accesses'   => $this->localeAccessRepository->countCustomLocaleAccesses(),
            'nb_custom_category_accesses' => $this->categoryAccessRepository->countCustomCategoryAccesses(),
        ];
    }
}
