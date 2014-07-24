<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use Symfony\Component\HttpFoundation\Request;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

/**
 * Hydrator for proposition (ORM support)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionHydrator implements HydratorInterface
{
    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $locale = $this->getCurrentLocaleCode();
        $records = [];
        foreach ($qb->getQuery()->execute() as $result) {
            $result['dataLocale']= $locale;
            $record = new ResultRecord($result);
            $records[] = $record;
        }

        return $records;
    }

    /**
     * Get current locale from datagrid parameters, then request parameters, then user config
     *
     * @return string
     */
    protected function getCurrentLocaleCode()
    {
        $dataLocale = $this->requestParams->get('dataLocale', null);
        if (!$dataLocale) {
            $dataLocale = $this->request->get('dataLocale', null);
        }
        if (!$dataLocale) {
            throw new \LogicException('Data locale should be passed to each result record');
        }

        return $dataLocale;
    }
}
