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
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
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
    /** @var ProductDraftRepositoryInterface */
    protected $draftRepository;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param ProductDraftRepositoryInterface     $draftRepository
     * @param ProjectRepositoryInterface          $projectRepository
     * @param AssetRepositoryInterface            $assetRepository
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(
        ProductDraftRepositoryInterface $draftRepository,
        ProjectRepositoryInterface $projectRepository,
        AssetRepositoryInterface $assetRepository,
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        $this->draftRepository = $draftRepository;
        $this->projectRepository = $projectRepository;
        $this->assetRepository = $assetRepository;
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_product_drafts'     => $this->draftRepository->countAll(),
            'nb_projects'           => $this->projectRepository->countAll(),
            'nb_assets'             => $this->assetRepository->countAll(),
            'nb_published_products' => $this->publishedRepository->countAll(),
        ];
    }
}
