<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Oro\Bundle\SecurityBundle\Form\EventListener\EntityRowSubscriber;

class ObjectNameType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oid', 'hidden',array(
                    'required' => true,
                )
            )->add('name', 'oro_acl_label',array(
                    'required' => false,
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_object_name';
    }
}