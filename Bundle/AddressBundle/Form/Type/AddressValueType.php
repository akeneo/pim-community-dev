<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;

class AddressValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_value';
    }
}
