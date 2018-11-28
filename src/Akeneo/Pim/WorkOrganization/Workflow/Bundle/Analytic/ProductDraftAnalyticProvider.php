<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Analytic;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * Returns count of Product Draft
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductDraftAnalyticProvider implements DataCollectorInterface
{
    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $draftRepository;

    /**
     * @param EntityWithValuesDraftRepositoryInterface $draftRepository
     */
    public function __construct(
        EntityWithValuesDraftRepositoryInterface $draftRepository
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
