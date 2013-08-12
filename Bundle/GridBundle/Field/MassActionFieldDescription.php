<?php

namespace Oro\Bundle\GridBundle\Field;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class MassActionFieldDescription extends FieldDescription
{
    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortFieldMapping()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParentAssociationMapping()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable()
    {
        return false;
    }
}
