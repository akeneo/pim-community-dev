<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

class ChoiceFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $value)
    {
        // TODO: Implement apply() method.
    }
}
