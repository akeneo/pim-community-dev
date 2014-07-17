<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDBODM;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\DateTimeTransformer;

/**
 * Hydrator for product history (MongoDB support)
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
        /** @var \Doctrine\MongoDB\Query $query */
        $query = $qb->hydrate(false)->getQuery();
        $productId = $query->getQuery()['query']['resourceId'];
        $publishedVersionId = $this->publishedRepository->getPublishedVersionIdByOriginalProductId($productId);
        $dateTransformer = new DateTimeTransformer();

        $rows = [];
        foreach ($query->execute() as $result) {
            $result['published'] = ($result['_id']->{'$id'} === $publishedVersionId);
            $result['loggedAt'] = isset($result['loggedAt']) ? $dateTransformer->transform($result['loggedAt']) : null;
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
