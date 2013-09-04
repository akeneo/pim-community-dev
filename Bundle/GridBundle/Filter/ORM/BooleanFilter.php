<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class BooleanFilter extends AbstractFilter
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

        $fieldExpression   = $this->createFieldExpression($field, $alias);
        $expressionFactory = $this->getExpressionFactory();
        $compareExpression = $expressionFactory->neq($fieldExpression, 'false');

        if ($this->isNullable()) {
            $summaryExpression = $expressionFactory->andX(
                $expressionFactory->isNotNull($fieldExpression),
                $compareExpression
            );
        } else {
            $summaryExpression = $compareExpression;
        }

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $expression = $summaryExpression;
                break;
            case BooleanFilterType::TYPE_NO:
            default:
                $expression = $expressionFactory->not($summaryExpression);
                break;
        }

        $this->applyFilterToClause($queryBuilder, $expression);
    }

    /**
     * @param mixed $data
     * @return array|bool
     */
    public function parseData($data)
    {
        $allowedValues = array(BooleanFilterType::TYPE_YES, BooleanFilterType::TYPE_NO);
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !$data['value']
            || !in_array($data['value'], $allowedValues)
        ) {
            return false;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => BooleanFilterType::NAME
        );
    }
}
