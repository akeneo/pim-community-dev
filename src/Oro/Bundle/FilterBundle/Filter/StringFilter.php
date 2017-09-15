<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Pim\Component\Catalog\Query\Filter\Operators;

class StringFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);
        $parameterName = $ds->generateParameterName($this->getName());

        $this->applyFilterToClause(
            $ds,
            $ds->expr()->comparison($this->get(FilterUtility::DATA_NAME_KEY), $operator, $parameterName, true)
        );

        $ds->setParameter($parameterName, $data['value']);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormType()
    {
        return TextFilterType::class;
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    protected function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !$data['value']) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        if (null !== $data['type'] && $data['type'] !== TextFilterType::TYPE_EQUAL) {
            $data['value'] = str_replace(['%', '_'], ['\\%', '\\_'], $data['value']);
        }
        $data['value'] = sprintf($this->getFormatByComparisonType($data['type']), $data['value']);

        return $data;
    }

    /**
     * Get operator string
     *
     * @param int $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $operatorTypes = [
            TextFilterType::TYPE_CONTAINS     => Operators::IS_LIKE,
            TextFilterType::TYPE_NOT_CONTAINS => Operators::IS_NOT_LIKE,
            TextFilterType::TYPE_EQUAL        => Operators::EQUALS,
            TextFilterType::TYPE_STARTS_WITH  => Operators::IS_LIKE,
            TextFilterType::TYPE_ENDS_WITH    => Operators::IS_LIKE,
            FilterType::TYPE_EMPTY            => Operators::IS_EMPTY,
            FilterType::TYPE_NOT_EMPTY        => Operators::IS_NOT_EMPTY,
            FilterType::TYPE_IN_LIST          => Operators::IN_LIST,
        ];

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : 'LIKE';
    }

    /**
     * Return value format depending on comparison type
     *
     * @param $comparisonType
     *
     * @return string
     */
    protected function getFormatByComparisonType($comparisonType)
    {
        // for other than listed comparison types - use default format
        switch ($comparisonType) {
            case TextFilterType::TYPE_STARTS_WITH:
                $format = '%s%%';
                break;
            case TextFilterType::TYPE_ENDS_WITH:
                $format = '%%%s';
                break;
            case TextFilterType::TYPE_CONTAINS:
            case TextFilterType::TYPE_NOT_CONTAINS:
                $format = '%%%s%%';
                break;
            default:
                $format = '%s';
        }

        return $format;
    }
}
