<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDBODM;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;

/**
 * Hydrator for proposition (MongoDB support)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionHydrator implements HydratorInterface
{
    /**
     * @var RequestParametersExtractorInterface
     */
    protected $requestParams;

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
        $query = $qb->hydrate(false)->getQuery();
        $rows = [];
        foreach ($query->execute() as $result) {
            $result['dataLocale'] =  $locale;
            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
