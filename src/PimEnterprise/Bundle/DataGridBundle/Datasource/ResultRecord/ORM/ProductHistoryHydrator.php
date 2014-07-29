<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Hydrator for product history (ORM support)
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductHistoryHydrator implements HydratorInterface
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
        /** @var \Doctrine\ORM\Query $query */
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
