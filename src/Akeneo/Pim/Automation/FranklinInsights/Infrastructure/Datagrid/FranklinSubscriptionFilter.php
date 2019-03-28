<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;

/**
 * Allows to apply a Franklin subscription filter on the Query builder.
 */
class FranklinSubscriptionFilter implements FilterInterface
{
    /** @var FilterInterface */
    private $baseFilter;

    /** @var ProductFilterUtility */
    private $filterUtility;

    /**
     * @param FilterInterface $baseFilter
     * @param ProductFilterUtility $filterUtility
     */
    public function __construct(FilterInterface $baseFilter, ProductFilterUtility $filterUtility)
    {
        $this->baseFilter = $baseFilter;
        $this->filterUtility = $filterUtility;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $filterDatasource, $data)
    {
        $filterValue = isset($data['value']) ? (bool) $data['value'] : null;

        if (null === $filterValue) {
            return false;
        }

        $this->filterUtility->applyFilter($filterDatasource, 'franklin_subscription', '=', $filterValue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params): void
    {
        $this->baseFilter->init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->baseFilter->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->baseFilter->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->baseFilter->getMetadata();
    }
}
