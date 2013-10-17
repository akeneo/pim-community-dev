<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class BooleanFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return BooleanFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $field             = $this->get('data_name');
        $compareExpression = $qb->expr()->neq($field, 'false');

        if ($this->getOr('nullable', false)) {
            $summaryExpression = $qb->expr()->andX(
                $qb->expr()->isNotNull($field),
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
                $expression = $qb->expr()->not($summaryExpression);
                break;
        }

        $this->applyFilterToClause($qb, $expression);

        return true;
    }

    /**
     * @param mixed $data
     *
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
}
