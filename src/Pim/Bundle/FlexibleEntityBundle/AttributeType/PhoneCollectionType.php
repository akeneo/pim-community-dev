<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * attribute type
 */
class PhoneCollectionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_phone_collection';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormData(FlexibleValueInterface $value)
    {
        return $value;
    }
}
