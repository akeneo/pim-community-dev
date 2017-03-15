<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Analytic;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;

/**
 * Returns count of Product Draft
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductDraftAnalyticProvider implements DataCollectorInterface
{
    /** @var ProductDraftRepositoryInterface */
    protected $draftRepository;

    /**
     * @param ProductDraftRepositoryInterface $draftRepository
     */
    public function __construct(
        ProductDraftRepositoryInterface $draftRepository
    ) {
        $this->draftRepository = $draftRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return ['nb_product_drafts' => $this->draftRepository->countAll()];
    }
}
