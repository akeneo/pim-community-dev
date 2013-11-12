<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilter extends ChoiceFilter
{
    /**
     * {@inheritDoc}
     */
    public function init($name, array $params)
    {
        $params[self::FRONTEND_TYPE_KEY] = 'choice';
        parent::init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return EntityFilterType::NAME;
    }
}
