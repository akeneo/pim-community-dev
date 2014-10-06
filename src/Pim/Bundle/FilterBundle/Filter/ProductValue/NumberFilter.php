<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Number filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter extends OroNumberFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
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
     * {@inheritdoc}
     */
    public function getOperator($type)
    {
        $operatorTypes = array(
            NumberFilterType::TYPE_EQUAL         => '=',
            NumberFilterType::TYPE_GREATER_EQUAL => '>=',
            NumberFilterType::TYPE_GREATER_THAN  => '>',
            NumberFilterType::TYPE_LESS_EQUAL    => '<=',
            NumberFilterType::TYPE_LESS_THAN     => '<',
            FilterType::TYPE_EMPTY               => 'EMPTY',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '=';
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !array_key_exists('type', $data)
            || (!is_numeric($data['value']) && FilterType::TYPE_EMPTY !== $data['type'])) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        return $data;
    }
}
