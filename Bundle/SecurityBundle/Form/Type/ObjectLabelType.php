<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Oro\Bundle\SecurityBundle\Form\EventListener\EntityRowSubscriber;

class ObjectLabelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_label';
    }

    public function getParent()
    {
        return 'hidden';
    }
}