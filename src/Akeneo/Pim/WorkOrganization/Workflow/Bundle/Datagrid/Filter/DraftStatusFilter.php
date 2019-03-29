<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class DraftStatusFilter implements FilterInterface
{
    /** @var ChoiceFilter */
    private $choiceFilter;

    /** @var ProductFilterUtility */
    private $filterUtility;

    /**
     * @param ChoiceFilter $choiceFilter
     * @param ProductFilterUtility $filterUtility
     */
    public function __construct(ChoiceFilter $choiceFilter, ProductFilterUtility $filterUtility)
    {
        $this->choiceFilter = $choiceFilter;
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

        $this->filterUtility->applyFilter($filterDatasource, 'draft_status', '=', $filterValue);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params): void
    {
        $this->choiceFilter->init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->choiceFilter->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->choiceFilter->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->choiceFilter->getMetadata();
    }
}
