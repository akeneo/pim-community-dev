<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return EntityFilterType::NAME;
    }
}
