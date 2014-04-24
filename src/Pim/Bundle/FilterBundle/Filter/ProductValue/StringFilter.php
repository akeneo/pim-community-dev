<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Filter\StringFilter as OroStringFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
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

        $this->util->applyFilterByAttribute(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $data['value'],
            $operator
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
            || (!$data['value'] && TextFilterType::TYPE_EMPTY !== $data['type'])) {
            return false;
        }

        $data['type']  = isset($data['type']) ? $data['type'] : null;
        $format = $ds->getFormatByComparisonType($data['type']);
        $data['value'] = sprintf($format, $data['value']);

        return $data;
    }
}
