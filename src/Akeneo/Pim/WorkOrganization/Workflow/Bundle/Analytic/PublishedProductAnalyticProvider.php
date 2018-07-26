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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

/**
 * Returns count of Published products
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class PublishedProductAnalyticProvider implements DataCollectorInterface
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return ['nb_published_products' => $this->publishedRepository->countAll()];
    }
}
