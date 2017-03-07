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

    /**
     * @param ProductDraftRepository              $draftRepository
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(
        ProductDraftRepository $draftRepository,
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        $this->draftRepository = $draftRepository;
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_product_drafts'                  => $this->draftRepository->countAll(),
            'nb_published_products'              => $this->publishedRepository->countAll()
        ];
    }
}
