<?php

namespace Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter as OroBooleanFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\BooleanFilterType;

/**
 * Boolean filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilter extends OroBooleanFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        if (!$data = $this->parseData($data)) {
            return false;
        }

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            '=',
            $data['value']
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $allowedValues = [BooleanFilterType::TYPE_YES, BooleanFilterType::TYPE_NO];
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !in_array($data['value'], $allowedValues, true)
        ) {
            return false;
        }

        $data['value'] = (bool) $data['value'];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return BooleanFilterType::class;
    }
}
