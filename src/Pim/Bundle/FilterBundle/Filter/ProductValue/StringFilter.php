<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\StringFilter as OroStringFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * String filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter extends OroStringFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->prepareData($ds, $data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data['value']
        );

        return true;
    }

    /**
     * @param FilterDatasourceAdapterInterface $ds
     * @param mixed                            $data
     *
     * @return array|bool
     */
    protected function prepareData(FilterDatasourceAdapterInterface $ds, $data)
    {
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !array_key_exists('type', $data)
            || (!$data['value'] && FilterType::TYPE_EMPTY !== $data['type'])) {
            return false;
        }
        $data['value'] = preg_quote($data['value']);

        $data['type']  = isset($data['type']) ? $data['type'] : null;

        if ('in' === $data['type']) {
            $data['value'] = explode(',', $data['value']);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !$data['value']) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOperator($type)
    {
        $operatorTypes = array(
            TextFilterType::TYPE_CONTAINS     => 'CONTAINS',
            TextFilterType::TYPE_NOT_CONTAINS => 'DOES NOT CONTAIN',
            TextFilterType::TYPE_EQUAL        => '=',
            TextFilterType::TYPE_STARTS_WITH  => 'START WITH',
            TextFilterType::TYPE_ENDS_WITH    => 'END WITH',
            FilterType::TYPE_EMPTY            => 'EMPTY',
            FilterType::TYPE_IN_LIST          => 'IN',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : 'LIKE';
    }
}
