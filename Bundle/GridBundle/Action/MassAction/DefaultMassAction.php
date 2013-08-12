<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

class DefaultMassAction extends AbstractMassAction
{
    /**
     * {@inheritDoc}
     */
    public function getFieldName()
    {
        $fieldName = parent::getFieldName();

        return $fieldName ?: 'id';
    }
}
