<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDBODM;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrator for product draft (MongoDB support)
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductDraftHydrator implements HydratorInterface
{
    /**
     * @var RequestParametersExtractorInterface
     */
    protected $extractor;

    /**
     * @param RequestParametersExtractorInterface $extractor
     */
    public function __construct(RequestParametersExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $locale = $this->extractor->getParameter('dataLocale');
        /** @var \Doctrine\MongoDB\Query $query */
        $query = $qb->getQuery();
        $rows = [];
        foreach ($query->execute() as $result) {
            $result->setDataLocale($locale);
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
