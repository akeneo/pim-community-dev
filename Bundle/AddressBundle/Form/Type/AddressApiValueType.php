<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;

class AddressApiValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_api_value';
    }
}
