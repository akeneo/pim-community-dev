<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class KeyIndicatorFilter extends ChoiceFilter
{
    private string $filterName;

    public function __construct(FormFactoryInterface $factory, FilterUtility $util, string $filterName)
    {
        $this->formFactory = $factory;
        $this->util = $util;
        parent::__construct($factory, $util);

        $this->filterName = $filterName;
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

        $this->util->applyFilter($filterDatasource, $this->filterName, '=', $filterValue);

        return true;
    }
}
