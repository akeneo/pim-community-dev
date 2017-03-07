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
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\AssetAnalyticProvider;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\LocaleAccessRepository;
use PimEnterprise\Bundle\AnalyticsBundle\Doctrine\ORM\Repository\ProductDraftRepository;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

/**
 * Collects the structure of the PIM Catalog (Enterprise features):
 * - Proposals count
 * - Projects count
 * - Assets count
 * - Number of locales with custom accesses
 * - Number of categories with custom accesses
 * - Number of attribute groups with custom accesses
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class DBDataCollector implements DataCollectorInterface
{
    /** @var ProductDraftRepository */
    protected $draftRepository;

    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /** @var LocaleAccessRepository */
    protected $localeAccessRepository;

    /** @var CategoryAccessRepository */
    protected $categAccessRepository;

    /** @var AttributeGroupAccessRepository */
    protected $groupAccessRepository;

    /**
     * @param ProductDraftRepository              $draftRepository
     * @param PublishedProductRepositoryInterface $publishedRepository
     * @param LocaleAccessRepository              $localeAccessRepository
     * @param CategoryAccessRepository            $categAccessRepository
     * @param AttributeGroupAccessRepository      $groupAccessRepository
     */
    public function __construct(
        ProductDraftRepository $draftRepository,
        PublishedProductRepositoryInterface $publishedRepository,
        LocaleAccessRepository $localeAccessRepository,
        CategoryAccessRepository $categAccessRepository,
        AttributeGroupAccessRepository $groupAccessRepository
    ) {
        $this->draftRepository = $draftRepository;
        $this->publishedRepository = $publishedRepository;
        $this->localeAccessRepository = $localeAccessRepository;
        $this->categAccessRepository = $categAccessRepository;
        $this->groupAccessRepository = $groupAccessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_product_drafts'                  => $this->draftRepository->countAll(),
            'nb_published_products'              => $this->publishedRepository->countAll(),
            'nb_custom_locale_accesses'          => $this->localeAccessRepository->countCustomAccesses(),
            'nb_custom_category_accesses'        => $this->categAccessRepository->countCustomAccesses(),
            'nb_custom_attribute_group_accesses' => $this->groupAccessRepository->countCustomAccesses(),
        ];
    }
}
