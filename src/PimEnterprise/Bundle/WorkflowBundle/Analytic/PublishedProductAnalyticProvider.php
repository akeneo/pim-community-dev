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
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

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
