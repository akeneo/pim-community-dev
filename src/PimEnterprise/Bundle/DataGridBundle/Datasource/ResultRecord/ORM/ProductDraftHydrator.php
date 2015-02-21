<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrator for product draft (ORM support)
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
        $records = [];
        foreach ($qb->getQuery()->execute() as $result) {
            $result = current($result);
            $result->setDataLocale($locale);
            $record = new ResultRecord($result);
            $records[] = $record;
        }

        return $records;
    }
}
