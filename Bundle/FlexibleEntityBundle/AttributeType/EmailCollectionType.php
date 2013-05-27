<?php

namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

class EmailCollectionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_email_collection';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormData(FlexibleValueInterface $value)
    {
        return $value;
    }
}
