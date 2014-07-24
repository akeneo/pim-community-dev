<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;

/**
 * Hydrator for proposition (ORM support)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionHydrator implements HydratorInterface
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
            $result['dataLocale']= $locale;
            $record = new ResultRecord($result);
            $records[] = $record;
        }

        return $records;
    }
}
