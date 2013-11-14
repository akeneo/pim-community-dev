<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class OptionSetCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_item_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_option_set_collection';
    }
}
