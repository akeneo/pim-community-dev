<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;

class StringFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->getOperator($data['type']);
        $parameterName = $this->getNewParameterName($queryBuilder);

        $this->applyFilterToClause(
            $queryBuilder,
            $this->createCompareFieldExpression($field, $alias, $operator, $parameterName)
        );

        /** @var $queryBuilder QueryBuilder */
        if ('=' == $operator) {
            $value = $data['value'];
        } else {
            $value = sprintf($this->getFormatByComparisonType($data['type']), $data['value']);
        }
        $queryBuilder->setParameter($parameterName, $value);
    }

    /**
     * @param mixed $data
     * @return array|bool
     */
    public function parseData($data)
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
     * @return string
     */
    public function getOperator($type)
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
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'format' => '%%%s%%',
            'form_type' => TextFilterType::NAME
        );
    }

    /**
     * Return value format depending on comparison type
     *
     * @param $comparisonType
     * @return string
     */
    public function getFormatByComparisonType($comparisonType)
    {
        // for other than listed comparison types - use default format
        switch ($comparisonType) {
            case TextFilterType::TYPE_STARTS_WITH:
                $format = '%s%%';
                break;
            case TextFilterType::TYPE_ENDS_WITH:
                $format = '%%%s';
                break;
            default:
                $format = $this->getOption('format');
        }

        return $format;
    }
}
