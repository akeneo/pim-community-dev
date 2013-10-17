<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType;

class DateTimeFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(QueryBuilder $qb, $value)
    {
        $data = $this->parseData($value);
        if ($data) {
            $operator      = $this->getOperator($data['type']);
            $parameterName = $this->generateQueryParameterName();

            $this->applyFilterToClause(
                $qb,
                $this->createComparisonExpression($this->get('data_name'), $operator, $parameterName)
            );

            $qb->setParameter($parameterName, sprintf($this->getFormatByComparisonType($data['type']), $data['value']));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormType()
    {
        return DateTimeRangeFilterType::NAME;
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
        $type = (int)$type;

        $operatorTypes = array(
            DateTimeRangeFilterType::TYPE_BETWEEN      => '>=',
            DateTimeRangeFilterType::TYPE_NOT_BETWEEN  => '<',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '>=';
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
            case DateTimeRangeFilterType::TYPE_BETWEEN:
                $format = '%s';
                break;
            case DateTimeRangeFilterType::TYPE_NOT_BETWEEN:
                $format = '%s';
                break;
            default:
                $format = '%s';
        }

        return $format;
    }
}
