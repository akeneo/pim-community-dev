<?php

namespace Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\StringFilter as OroStringFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;

/**
 * String filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter extends OroStringFilter
{
    /** @var array */
    protected $operatorTypes = [
        TextFilterType::TYPE_CONTAINS     => Operators::CONTAINS,
        TextFilterType::TYPE_NOT_CONTAINS => Operators::DOES_NOT_CONTAIN,
        TextFilterType::TYPE_EQUAL        => Operators::EQUALS,
        TextFilterType::TYPE_STARTS_WITH  => Operators::STARTS_WITH,
        TextFilterType::TYPE_ENDS_WITH    => Operators::ENDS_WITH,
        FilterType::TYPE_EMPTY            => Operators::IS_EMPTY,
        FilterType::TYPE_NOT_EMPTY        => Operators::IS_NOT_EMPTY,
        FilterType::TYPE_IN_LIST          => Operators::IN_LIST,
    ];

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
        if (!is_array($data) || !array_key_exists('value', $data) || !array_key_exists('type', $data)) {
            return false;
        }

        if (in_array($data['type'], [FilterType::TYPE_EMPTY, FilterType::TYPE_NOT_EMPTY])) {
            $data['value'] = '';

            return $data;
        }

        if (null === $data['value'] || '' === $data['value']) {
            return false;
        }

        if (FilterType::TYPE_IN_LIST === $data['type']) {
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
        if (!isset($this->operatorTypes[$type])) {
            throw new \InvalidArgumentException(sprintf('Operator %s is not supported', $type));
        }

        return $this->operatorTypes[$type];
    }
}
