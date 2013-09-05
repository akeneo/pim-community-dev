<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PermissionCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['privileges_config'] = $options['options']['privileges_config'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_permission_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }
}
