<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * attribute type
 */
class EmailCollectionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_email_collection';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormData(FlexibleValueInterface $value)
    {
        return $value;
    }
}
