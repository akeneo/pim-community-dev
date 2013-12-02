<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;

class StringFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if ($data) {
            $operator      = $this->getOperator($data['type']);
            $parameterName = $this->generateQueryParameterName();

            $this->applyFilterToClause(
                $qb,
                $this->createComparisonExpression($this->get(self::DATA_NAME_KEY), $operator, $parameterName)
            );

            $qb->setParameter($parameterName, $data['value']);

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormType()
    {
        return TextFilterType::NAME;
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

        $data['type']  = isset($data['type']) ? $data['type'] : null;
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
        $type = (int)$type;

        $operatorTypes = array(
            TextFilterType::TYPE_CONTAINS     => 'LIKE',
            TextFilterType::TYPE_NOT_CONTAINS => 'NOT LIKE',
            TextFilterType::TYPE_EQUAL        => '=',
            TextFilterType::TYPE_STARTS_WITH  => 'LIKE',
            TextFilterType::TYPE_ENDS_WITH    => 'LIKE',
        );

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
