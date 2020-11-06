<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilter extends ChoiceFilter
{
    /**
     * {@inheritDoc}
     */
    public function init(string $name, array $params): void
    {
        $params[FilterUtility::FRONTEND_TYPE_KEY] = 'choice';
        parent::init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType(): string
    {
        return EntityFilterType::class;
    }
}
