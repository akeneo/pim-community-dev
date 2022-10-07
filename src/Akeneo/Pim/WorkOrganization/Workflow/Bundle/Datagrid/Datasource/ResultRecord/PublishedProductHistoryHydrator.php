<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ResultRecord;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Doctrine\ORM\Query;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrator for product history (ORM support)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class PublishedProductHistoryHydrator implements HydratorInterface
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(PublishedProductRepositoryInterface $publishedRepository)
    {
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        /** @var Query $query */
        $query = $qb->getQuery();
        $productId = (int) $query->getParameter('objectId')->getValue();
        $publishedVersionId = $this->publishedRepository->getPublishedVersionIdByOriginalProductId($productId);

        $rows = [];
        foreach ($query->getArrayResult() as $result) {
            $result['published'] = ($result['id'] === $publishedVersionId);
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
